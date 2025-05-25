<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Require2FA
{
    /**
     * Routes that should bypass 2FA verification.
     *
     * @var array<string>
     */
    protected $except = [
        '2fa/setup',
        '2fa/confirm',
        '2fa/challenge',
        '2fa/resend',
        'logout',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Skip 2FA check for excepted routes
        if ($this->shouldBypass($request)) {
            return $next($request);
        }

        // Check if 2FA is enabled for the user
        if ($user->two_factor_enabled) {
            // Check if user has completed 2FA setup
            if (!$user->two_factor_secret) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'Two-factor authentication setup required.',
                        'redirect' => route('2fa.setup'),
                    ], 403);
                }

                return redirect()->route('2fa.setup')
                    ->with('warning', 'You must set up two-factor authentication to continue.');
            }

            // Check if user has verified 2FA for current session
            if (!session('2fa_verified')) {
                // Store intended URL
                if (!$request->is('2fa/*')) {
                    session(['url.intended' => $request->url()]);
                }

                // Log 2FA challenge request
                audit_log('2fa_challenge_required', [
                    'user_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'attempted_url' => $request->url(),
                ]);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'Two-factor authentication required.',
                        'redirect' => route('2fa.challenge'),
                    ], 403);
                }

                return redirect()->route('2fa.challenge')
                    ->with('warning', 'Please verify your two-factor authentication code to continue.');
            }
        }
        // Check if 2FA is required but not enabled
        elseif ($this->isRequiredFor($user)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Two-factor authentication must be enabled for your account.',
                    'redirect' => route('2fa.setup'),
                ], 403);
            }

            return redirect()->route('2fa.setup')
                ->with('warning', 'You must enable two-factor authentication to continue.');
        }

        return $next($request);
    }

    /**
     * Determine if the request should bypass 2FA verification.
     */
    protected function shouldBypass(Request $request): bool
    {
        foreach ($this->except as $path) {
            if ($request->is($path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if 2FA is required for the given user.
     */
    protected function isRequiredFor($user): bool
    {
        // Check if user has a role that requires 2FA
        $requiredRoles = ['admin', 'manager'];
        if (in_array($user->role?->name, $requiredRoles)) {
            return true;
        }

        // Check if user has permissions that require 2FA
        $requiredPermissions = [
            'manage_users',
            'manage_roles',
            'manage_permissions',
            'manage_settings',
        ];

        foreach ($requiredPermissions as $permission) {
            if ($user->hasPermission($permission)) {
                return true;
            }
        }

        // Check if global 2FA requirement is enabled
        if (config('auth.2fa_required')) {
            return true;
        }

        return false;
    }

    /**
     * Add paths to the bypass list.
     */
    public function addBypassPaths(array $paths): void
    {
        $this->except = array_merge($this->except, $paths);
    }

    /**
     * Get the list of bypass paths.
     */
    public function getBypassPaths(): array
    {
        return $this->except;
    }
}
