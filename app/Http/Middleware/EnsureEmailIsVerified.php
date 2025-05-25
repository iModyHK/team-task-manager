<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() ||
            ($request->user() instanceof MustVerifyEmail &&
            !$request->user()->hasVerifiedEmail())) {

            // Log unverified email access attempt
            if ($request->user()) {
                audit_log('unverified_email_access', [
                    'user_id' => $request->user()->id,
                    'ip_address' => $request->ip(),
                    'attempted_url' => $request->url(),
                ]);
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Your email address is not verified.',
                    'verification_url' => route('verification.notice'),
                ], 403);
            }

            return redirect()->route('verification.notice')
                ->with('warning', 'You must verify your email address to access this page.');
        }

        // If email was recently verified, show success message
        if ($request->user()->wasRecentlyVerified()) {
            session()->flash('status', 'Your email has been verified successfully.');
        }

        return $next($request);
    }
}
