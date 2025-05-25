<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Log authenticated user attempting to access guest-only route
                if (function_exists('activity_log')) {
                    activity_log('guest_route_access_attempt', [
                        'user_id' => Auth::guard($guard)->id(),
                        'ip_address' => $request->ip(),
                        'attempted_url' => $request->url(),
                        'guard' => $guard,
                    ]);
                }

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'message' => 'Already authenticated.',
                        'redirect' => RouteServiceProvider::HOME,
                    ], 200);
                }

                // Check if there's an intended URL
                $intended = session()->pull('url.intended');
                if ($intended && $intended !== route('login')) {
                    return redirect($intended);
                }

                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }

    /**
     * Get the path the user should be redirected to.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $guard
     * @return string
     */
    protected function redirectTo(Request $request, ?string $guard = null): string
    {
        // Check if user has admin role
        if ($guard && Auth::guard($guard)->check() && Auth::guard($guard)->user()->hasRole('admin')) {
            return route('admin.dashboard');
        }

        // Check if there's a custom dashboard based on user role
        if ($guard && Auth::guard($guard)->check()) {
            $user = Auth::guard($guard)->user();
            $roleDashboards = [
                'manager' => 'manager.dashboard',
                'team_leader' => 'team.dashboard',
            ];

            foreach ($roleDashboards as $role => $dashboard) {
                if ($user->hasRole($role)) {
                    return route($dashboard);
                }
            }
        }

        return RouteServiceProvider::HOME;
    }

    /**
     * Get the guards that should be checked.
     *
     * @return array
     */
    protected function guards(): array
    {
        return config('auth.guards', []);
    }

    /**
     * Determine if the user should be redirected.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $guard
     * @return bool
     */
    protected function shouldRedirect(Request $request, ?string $guard = null): bool
    {
        return Auth::guard($guard)->check() && 
            !$request->is('logout') && 
            !$request->is('password/*');
    }
}
