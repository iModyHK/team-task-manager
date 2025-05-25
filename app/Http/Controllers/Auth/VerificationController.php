<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    /**
     * Show the email verification notice.
     */
    public function show(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        return view('auth.verify-email');
    }

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verify(EmailVerificationRequest $request)
    {
        try {
            if ($request->user()->hasVerifiedEmail()) {
                return redirect()->intended(route('dashboard').'?verified=1');
            }

            if ($request->user()->markEmailAsVerified()) {
                event(new Verified($request->user()));

                // Log email verification
                activity_log('email_verified', [
                    'user_id' => $request->user()->id,
                    'ip_address' => $request->ip(),
                ]);
            }

            return redirect()->intended(route('dashboard').'?verified=1')
                ->with('status', 'Your email has been verified.');

        } catch (\Exception $e) {
            report($e);
            return back()->withErrors([
                'error' => 'An error occurred while verifying your email.',
            ]);
        }
    }

    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request)
    {
        try {
            if ($request->user()->hasVerifiedEmail()) {
                return redirect()->intended(route('dashboard'));
            }

            // Check if we've sent too many verification emails
            if ($this->hasTooManyVerificationAttempts($request)) {
                return back()->withErrors([
                    'email' => 'Too many verification attempts. Please try again later.',
                ]);
            }

            $request->user()->sendEmailVerificationNotification();

            // Log verification email resent
            activity_log('verification_email_resent', [
                'user_id' => $request->user()->id,
                'ip_address' => $request->ip(),
            ]);

            return back()->with('status', 'verification-link-sent');

        } catch (\Exception $e) {
            report($e);
            return back()->withErrors([
                'error' => 'An error occurred while sending the verification email.',
            ]);
        }
    }

    /**
     * Determine if the user has too many verification attempts.
     */
    protected function hasTooManyVerificationAttempts(Request $request): bool
    {
        $key = 'verification_attempts_' . $request->user()->id;
        $maxAttempts = 5;
        $decayMinutes = 60;

        if ($attempts = cache()->get($key, 0) >= $maxAttempts) {
            return true;
        }

        cache()->put($key, cache()->get($key, 0) + 1, now()->addMinutes($decayMinutes));
        return false;
    }

    /**
     * Get the verification URL for the given user.
     */
    protected function verificationUrl(User $user): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );
    }
}
