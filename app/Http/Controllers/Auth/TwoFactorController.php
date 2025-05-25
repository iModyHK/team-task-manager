<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the 2FA setup form.
     */
    public function show()
    {
        $user = auth()->user();

        if ($user->two_factor_enabled) {
            return view('auth.2fa.status', [
                'enabled' => true,
            ]);
        }

        $google2fa = new Google2FA();
        
        // Generate secret key if not exists
        if (!$user->two_factor_secret) {
            $user->two_factor_secret = $google2fa->generateSecretKey();
            $user->save();
        }

        // Generate QR code URL
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->two_factor_secret
        );

        return view('auth.2fa.setup', [
            'qrCodeUrl' => $qrCodeUrl,
            'secret' => $user->two_factor_secret,
        ]);
    }

    /**
     * Enable 2FA for the authenticated user.
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
            'current_password' => ['required', 'string'],
        ]);

        $user = $request->user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'The provided password is incorrect.',
            ]);
        }

        try {
            // Verify the 2FA code
            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey(
                $user->two_factor_secret,
                $request->code
            );

            if (!$valid) {
                return back()->withErrors([
                    'code' => 'The provided two-factor code is invalid.',
                ]);
            }

            $user->update([
                'two_factor_enabled' => true,
            ]);

            // Log 2FA enabled
            audit_log('2fa_enabled', [
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
            ]);

            return redirect()->route('profile.2fa')
                ->with('status', 'Two-factor authentication has been enabled.');

        } catch (\Exception $e) {
            report($e);
            return back()->withErrors([
                'error' => 'An error occurred while enabling two-factor authentication.',
            ]);
        }
    }

    /**
     * Disable 2FA for the authenticated user.
     */
    public function disable(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
        ]);

        $user = $request->user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'The provided password is incorrect.',
            ]);
        }

        try {
            $user->update([
                'two_factor_enabled' => false,
                'two_factor_secret' => null,
            ]);

            // Log 2FA disabled
            audit_log('2fa_disabled', [
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
            ]);

            return redirect()->route('profile.2fa')
                ->with('status', 'Two-factor authentication has been disabled.');

        } catch (\Exception $e) {
            report($e);
            return back()->withErrors([
                'error' => 'An error occurred while disabling two-factor authentication.',
            ]);
        }
    }

    /**
     * Show the 2FA challenge form during login.
     */
    public function showChallenge()
    {
        if (!session('auth.2fa.user_id')) {
            return redirect()->route('login');
        }

        return view('auth.2fa.challenge');
    }

    /**
     * Verify the 2FA code during login.
     */
    public function verifyChallenge(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        try {
            $userId = session('auth.2fa.user_id');
            $user = User::findOrFail($userId);

            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey(
                $user->two_factor_secret,
                $request->code
            );

            if (!$valid) {
                return back()->withErrors([
                    'code' => 'The provided two-factor code is invalid.',
                ]);
            }

            // Clear 2FA session data
            session()->forget('auth.2fa');

            // Complete the login
            auth()->login($user);

            // Log successful 2FA verification
            activity_log('2fa_verified', [
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
            ]);

            return redirect()->intended(route('dashboard'));

        } catch (\Exception $e) {
            report($e);
            return back()->withErrors([
                'error' => 'An error occurred while verifying the two-factor code.',
            ]);
        }
    }

    /**
     * Generate recovery codes for the authenticated user.
     */
    public function generateRecoveryCodes(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
        ]);

        $user = $request->user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'The provided password is incorrect.',
            ]);
        }

        try {
            $recoveryCodes = collect(range(1, 8))->map(function () {
                return Str::random(10);
            })->all();

            $user->update([
                'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
            ]);

            // Log recovery codes generated
            audit_log('2fa_recovery_codes_generated', [
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
            ]);

            return view('auth.2fa.recovery-codes', [
                'recoveryCodes' => $recoveryCodes,
            ]);

        } catch (\Exception $e) {
            report($e);
            return back()->withErrors([
                'error' => 'An error occurred while generating recovery codes.',
            ]);
        }
    }
}
