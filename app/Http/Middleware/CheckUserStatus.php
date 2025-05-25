<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            // Check if user is active
            if ($user->status !== 'active') {
                // Log inactive user access attempt
                audit_log('inactive_user_access', [
                    'user_id' => $user->id,
                    'status' => $user->status,
                    'ip_address' => $request->ip(),
                    'attempted_url' => $request->url(),
                ]);

                // Logout the user
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => $this->getStatusMessage($user->status),
                    ], 403);
                }

                return redirect()->route('login')->withErrors([
                    'email' => $this->getStatusMessage($user->status),
                ]);
            }

            // Check if password needs to be changed
            if ($user->force_password_change) {
                if (!$request->is('password/change') && !$request->is('logout')) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'error' => 'You must change your password before continuing.',
                            'redirect' => route('password.change'),
                        ], 403);
                    }

                    return redirect()->route('password.change')
                        ->with('warning', 'You must change your password before continuing.');
                }
            }

            // Check if password is expired
            if ($user->password_expires_at && $user->password_expires_at->isPast()) {
                if (!$request->is('password/expired') && !$request->is('logout')) {
                    // Log password expired access attempt
                    audit_log('expired_password_access', [
                        'user_id' => $user->id,
                        'ip_address' => $request->ip(),
                        'attempted_url' => $request->url(),
                    ]);

                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'error' => 'Your password has expired. Please reset it.',
                            'redirect' => route('password.expired'),
                        ], 403);
                    }

                    return redirect()->route('password.expired')
                        ->with('warning', 'Your password has expired. Please reset it.');
                }
            }

            // Check if account is locked
            if ($user->is_locked) {
                if ($user->locked_until && $user->locked_until->isFuture()) {
                    // Log locked account access attempt
                    audit_log('locked_account_access', [
                        'user_id' => $user->id,
                        'ip_address' => $request->ip(),
                        'attempted_url' => $request->url(),
                        'locked_until' => $user->locked_until,
                    ]);

                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'error' => 'Your account is temporarily locked. Please try again later.',
                        ], 403);
                    }

                    return redirect()->route('login')->withErrors([
                        'email' => 'Your account is temporarily locked. Please try again later.',
                    ]);
                } else {
                    // Unlock account if lock period has expired
                    $user->update([
                        'is_locked' => false,
                        'locked_until' => null,
                        'failed_login_attempts' => 0,
                    ]);
                }
            }

            // Update last activity timestamp
            $user->touch('last_active_at');
        }

        return $next($request);
    }

    /**
     * Get the appropriate message for the user's status.
     */
    protected function getStatusMessage(string $status): string
    {
        return match ($status) {
            'inactive' => 'Your account is currently inactive. Please contact the administrator.',
            'suspended' => 'Your account has been suspended. Please contact the administrator.',
            'banned' => 'Your account has been banned. Please contact the administrator.',
            'pending' => 'Your account is pending approval. Please wait for administrator approval.',
            default => 'Your account is not active. Please contact the administrator.',
        };
    }

    /**
     * Check if the request path should bypass status checks.
     */
    protected function shouldBypass(Request $request): bool
    {
        $bypassPaths = [
            'logout',
            'password/reset/*',
            'email/verify/*',
            'account/reactivate/*',
        ];

        foreach ($bypassPaths as $path) {
            if ($request->is($path)) {
                return true;
            }
        }

        return false;
    }
}
