<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (!$request->expectsJson()) {
            // Store the intended URL in the session
            session()->put('url.intended', $request->url());
            
            return route('login');
        }

        return null;
    }

    /**
     * Handle an unauthenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $guards
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function unauthenticated($request, array $guards)
    {
        if ($request->ajax() || $request->wantsJson()) {
            abort(401, 'Unauthenticated.');
        }

        // Log failed authentication attempt
        audit_log('authentication_failed', [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'intended_url' => $request->url(),
        ]);

        throw new \Illuminate\Auth\AuthenticationException(
            'Unauthenticated.', $guards, $this->redirectTo($request)
        );
    }
}
