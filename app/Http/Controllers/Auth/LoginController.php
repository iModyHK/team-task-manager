<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     */
    public function login(LoginRequest $request)
    {
        try {
            $credentials = $request->only('email', 'password');
            
            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $user = Auth::user();
                
                // Check if user is active
                if ($user->status !== 'active') {
                    Auth::logout();
                    return back()->withErrors([
                        'email' => 'Your account is not active. Please contact the administrator.',
                    ]);
                }

                // Create user session record
                UserSession::create([
                    'user_id' => $user->id,
                    'session_token' => Str::random(60),
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                    'last_active' => now(),
                ]);

                // Update last login timestamp
                $user->update(['last_login' => now()]);

                // Log successful login
                activity_log('login_success', [
                    'user_id' => $user->id,
                    'ip_address' => $request->ip(),
                ]);

                // Check if 2FA is enabled
                if ($user->two_factor_enabled) {
                    return redirect()->route('auth.2fa.challenge');
                }

                return redirect()->intended(route('dashboard'));
            }

            // Log failed login attempt
            audit_log('login_failed', [
                'email' => $request->email,
                'ip_address' => $request->ip(),
            ]);

            return back()->withErrors([
                'email' => 'These credentials do not match our records.',
            ])->withInput($request->only('email', 'remember'));

        } catch (\Exception $e) {
            report($e);
            return back()->withErrors([
                'email' => 'An error occurred while attempting to log in.',
            ]);
        }
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        try {
            // Get the current user before logging out
            $user = Auth::user();

            // Delete the current session record
            if ($user) {
                UserSession::where([
                    'user_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ])->delete();

                // Log logout
                activity_log('logout', [
                    'user_id' => $user->id,
                    'ip_address' => $request->ip(),
                ]);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');

        } catch (\Exception $e) {
            report($e);
            return redirect()->route('login')->withErrors([
                'error' => 'An error occurred while logging out.',
            ]);
        }
    }

    /**
     * Show the two-factor authentication challenge form.
     */
    public function showTwoFactorForm()
    {
        return view('auth.2fa-challenge');
    }

    /**
     * Verify the two-factor authentication code.
     */
    public function verifyTwoFactor(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = Auth::user();

        if (!$user || !$user->two_factor_enabled) {
            return redirect()->route('login');
        }

        if (!$this->verify2FACode($user, $request->code)) {
            return back()->withErrors([
                'code' => 'The provided two-factor authentication code was invalid.',
            ]);
        }

        $request->session()->put('auth.two_factor_verified', true);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Verify the 2FA code against the user's secret.
     */
    protected function verify2FACode($user, $code)
    {
        // In a real application, you would use a 2FA library like Google Authenticator
        // This is just a placeholder implementation
        return $code === '123456'; // Replace with actual 2FA verification
    }
}
