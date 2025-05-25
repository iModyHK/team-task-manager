<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    /**
     * Show the form to request a password reset link.
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Send a reset link to the given user.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                throw ValidationException::withMessages([
                    'email' => [trans('passwords.user')],
                ]);
            }

            // Check if user is active
            if ($user->status !== 'active') {
                throw ValidationException::withMessages([
                    'email' => ['This account is not active.'],
                ]);
            }

            // Generate reset token
            $token = Str::random(64);

            // Create password reset record
            PasswordReset::create([
                'user_id' => $user->id,
                'reset_token' => $token,
                'requested_ip' => $request->ip(),
                'requested_at' => now(),
            ]);

            // Send password reset email
            $status = Password::sendResetLink(
                $request->only('email'),
                function ($user, $token) {
                    $user->notify(new \App\Notifications\ResetPassword($token));
                }
            );

            // Log password reset request
            audit_log('password_reset_requested', [
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
            ]);

            return $status === Password::RESET_LINK_SENT
                ? back()->with('status', __($status))
                : back()->withErrors(['email' => __($status)]);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            report($e);
            return back()->withErrors([
                'email' => 'An error occurred while sending the password reset link.',
            ]);
        }
    }

    /**
     * Get the response for a successful password reset link.
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        return back()->with('status', trans($response));
    }

    /**
     * Get the response for a failed password reset link.
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => trans($response)]);
    }

    /**
     * Validate the email for the given request.
     */
    protected function validateEmail(Request $request)
    {
        $request->validate([
            'email' => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    $user = User::where('email', $value)->first();
                    
                    if ($user && $user->status !== 'active') {
                        $fail('This account is not active.');
                    }
                },
            ],
        ]);
    }

    /**
     * Get the broker to be used during password reset.
     */
    public function broker()
    {
        return Password::broker();
    }
}
