<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset as PasswordResetEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class ResetPasswordController extends Controller
{
    /**
     * Show the reset password form.
     */
    public function showResetForm(Request $request, string $token = null)
    {
        return view('auth.passwords.reset')->with([
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Reset the given user's password.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            // Find the password reset record
            $passwordReset = PasswordReset::where([
                'reset_token' => $request->token,
                'used' => false,
            ])->first();

            if (!$passwordReset) {
                throw ValidationException::withMessages([
                    'email' => [trans('passwords.token')],
                ]);
            }

            // Find the user
            $user = User::where('email', $request->email)->first();

            if (!$user || $user->id !== $passwordReset->user_id) {
                throw ValidationException::withMessages([
                    'email' => [trans('passwords.user')],
                ]);
            }

            // Check if token is expired (24 hours)
            if ($passwordReset->requested_at->addHours(24)->isPast()) {
                throw ValidationException::withMessages([
                    'email' => [trans('passwords.token')],
                ]);
            }

            // Reset the password
            $user->forceFill([
                'password' => Hash::make($request->password),
            ])->save();

            // Mark the reset token as used
            $passwordReset->update([
                'used' => true,
                'used_at' => now(),
            ]);

            // Log password reset
            audit_log('password_reset_completed', [
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
            ]);

            // Fire password reset event
            event(new PasswordResetEvent($user));

            // Generate a new remember token
            $user->setRememberToken(Str::random(60));
            $user->save();

            // Invalidate all existing sessions for security
            $user->sessions()->delete();

            // Log the user in
            auth()->login($user);

            return redirect()->route('dashboard')
                ->with('status', 'Your password has been reset successfully.');

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            report($e);
            return back()->withErrors([
                'email' => 'An error occurred while resetting your password.',
            ]);
        }
    }

    /**
     * Get the password reset validation rules.
     */
    protected function rules(): array
    {
        return [
            'token' => 'required',
            'email' => ['required', 'email'],
            'password' => [
                'required',
                'confirmed',
                Rules\Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ];
    }

    /**
     * Get the password reset validation error messages.
     */
    protected function validationErrorMessages(): array
    {
        return [
            'token.required' => 'The reset token is required.',
            'email.required' => 'The email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'The password is required.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }

    /**
     * Get the response for a successful password reset.
     */
    protected function sendResetResponse(Request $request, $response)
    {
        return redirect()->route('login')
            ->with('status', trans($response));
    }

    /**
     * Get the response for a failed password reset.
     */
    protected function sendResetFailedResponse(Request $request, $response)
    {
        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => trans($response)]);
    }

    /**
     * Get the broker to be used during password reset.
     */
    public function broker()
    {
        return Password::broker();
    }
}
