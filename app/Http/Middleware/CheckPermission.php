<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (!$request->user()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized.'], 401);
            }
            return redirect()->route('login');
        }

        // Check if user has any of the required permissions
        foreach ($permissions as $permission) {
            if ($request->user()->hasPermission($permission)) {
                return $next($request);
            }
        }

        // Log unauthorized access attempt
        audit_log('unauthorized_access', [
            'user_id' => $request->user()->id,
            'ip_address' => $request->ip(),
            'required_permissions' => $permissions,
            'attempted_url' => $request->url(),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'error' => 'You do not have the required permission to access this resource.',
            ], 403);
        }

        return redirect()->back()->withErrors([
            'error' => 'You do not have the required permission to access this resource.',
        ]);
    }
}
