<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized.'], 401);
            }
            return redirect()->route('login');
        }

        // Check if user has any of the required roles
        foreach ($roles as $role) {
            if ($request->user()->hasRole($role)) {
                return $next($request);
            }
        }

        // Log unauthorized access attempt
        audit_log('unauthorized_access', [
            'user_id' => $request->user()->id,
            'ip_address' => $request->ip(),
            'required_roles' => $roles,
            'attempted_url' => $request->url(),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'error' => 'You do not have the required role to access this resource.',
            ], 403);
        }

        return redirect()->back()->withErrors([
            'error' => 'You do not have the required role to access this resource.',
        ]);
    }
}
