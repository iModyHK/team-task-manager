<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Symfony\Component\HttpFoundation\Response;

class ValidateSignature
{
    /**
     * The names of the query string parameters that should be ignored.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Common parameters that shouldn't invalidate the signature
        'fbclid',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'gclid',
        '_ga',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $relative = null): Response
    {
        try {
            $this->ensureValidSignature($request, $relative !== null);

            return $next($request);
        } catch (InvalidSignatureException $e) {
            // Log invalid signature attempt
            audit_log('invalid_signature_access', [
                'user_id' => $request->user()?->id,
                'ip_address' => $request->ip(),
                'attempted_url' => $request->url(),
                'query_params' => $request->query(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Invalid signature.',
                    'message' => 'The link has expired or is invalid.',
                ], 403);
            }

            abort(403, 'Invalid signature.');
        }
    }

    /**
     * Ensure the request has a valid signature.
     */
    protected function ensureValidSignature(Request $request, bool $absolute = true): void
    {
        // Create a new request instance without ignored parameters
        $requestWithoutIgnored = $this->createRequestWithoutIgnoredParameters($request);

        if (! $requestWithoutIgnored->hasValidSignature($absolute)) {
            throw new InvalidSignatureException;
        }
    }

    /**
     * Create a new request instance without ignored parameters.
     */
    protected function createRequestWithoutIgnoredParameters(Request $request): Request
    {
        $queryParams = collect($request->query())
            ->reject(function ($value, $key) {
                return in_array($key, $this->except);
            })
            ->all();

        $newRequest = Request::create(
            $request->url(),
            $request->method(),
            $queryParams,
            $request->cookie(),
            $request->file(),
            $request->server(),
            $request->getContent()
        );

        $newRequest->headers->replace($request->headers->all());
        $newRequest->setJson($request->json());

        return $newRequest;
    }

    /**
     * Add parameters to the ignored list.
     */
    public function addIgnoredParams(array $params): void
    {
        $this->except = array_merge($this->except, $params);
    }

    /**
     * Get the list of ignored parameters.
     */
    public function getIgnoredParams(): array
    {
        return $this->except;
    }

    /**
     * Remove parameters from the ignored list.
     */
    public function removeIgnoredParams(array $params): void
    {
        $this->except = array_diff($this->except, $params);
    }

    /**
     * Check if a parameter is in the ignored list.
     */
    public function isIgnoredParam(string $param): bool
    {
        return in_array($param, $this->except);
    }
}
