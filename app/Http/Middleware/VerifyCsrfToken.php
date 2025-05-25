<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Closure;
use Illuminate\Support\Str;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Webhook endpoints
        'webhooks/*',
        
        // Public API endpoints
        'api/public/*',
        
        // Health check endpoints
        'health',
        'health/*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Illuminate\Session\TokenMismatchException
     */
    public function handle($request, Closure $next)
    {
        // Skip CSRF verification for excluded paths
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        try {
            return parent::handle($request, $next);
        } catch (\Illuminate\Session\TokenMismatchException $e) {
            // Log CSRF token mismatch
            if (function_exists('audit_log')) {
                audit_log('csrf_token_mismatch', [
                    'user_id' => $request->user()?->id,
                    'ip_address' => $request->ip(),
                    'url' => $request->url(),
                    'method' => $request->method(),
                    'user_agent' => $request->userAgent(),
                ]);
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'CSRF token mismatch.',
                    'message' => 'Please refresh the page and try again.',
                ], 419);
            }

            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['error' => 'The page has expired. Please try again.']);
        }
    }

    /**
     * Determine if the request should skip CSRF verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldSkip($request): bool
    {
        // Check if the request URI matches any of the except patterns
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            $path = $request->path();
            if ($path === $except || Str::is($except, $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add URIs to the CSRF verification exception list.
     *
     * @param  string|array  $paths
     * @return void
     */
    public function addExcept($paths): void
    {
        $paths = is_array($paths) ? $paths : [$paths];
        $this->except = array_merge($this->except, $paths);
    }

    /**
     * Remove URIs from the CSRF verification exception list.
     *
     * @param  string|array  $paths
     * @return void
     */
    public function removeExcept($paths): void
    {
        $paths = is_array($paths) ? $paths : [$paths];
        $this->except = array_diff($this->except, $paths);
    }

    /**
     * Get the list of URIs excluded from CSRF verification.
     *
     * @return array
     */
    public function getExcept(): array
    {
        return $this->except;
    }
}
