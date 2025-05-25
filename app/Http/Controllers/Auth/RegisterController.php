<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request for the application.
     */
    public function register(RegisterRequest $request)
    {
        try {
            DB::beginTransaction();

            // Get default user role
            $defaultRole = Role::where('name', 'user')->first();
            if (!$defaultRole) {
                throw new \Exception('Default user role not found.');
            }

            // Create the user
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'full_name' => $request->full_name,
                'role_id' => $defaultRole->id,
                'status' => 'active',
            ]);

            // Create default user settings
            Setting::create([
                'user_id' => $user->id,
                'dark_mode' => false,
                'language' => app()->getLocale(),
                'notification_preferences' => json_encode([
                    'email_notifications' => true,
                    'task_updates' => true,
                    'team_updates' => true,
                    'mention_notifications' => true,
                ]),
            ]);

            // Log the registration
            activity_log('user_registered', [
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            // Fire registered event
            event(new Registered($user));

            // Log the user in
            Auth::login($user);

            // Create initial session record
            $user->sessions()->create([
                'session_token' => str_random(60),
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'last_active' => now(),
            ]);

            return redirect()->route('verification.notice');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return back()->withInput()->withErrors([
                'error' => 'An error occurred during registration. Please try again.',
            ]);
        }
    }

    /**
     * Show the email verification notice.
     */
    public function showVerificationNotice()
    {
        return view('auth.verify-email');
    }

    /**
     * Resend the email verification notification.
     */
    public function resendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            throw new AuthorizationException;
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));

            // Log email verification
            activity_log('email_verified', [
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
            ]);
        }

        return redirect()->intended(route('dashboard'))
            ->with('status', 'email-verified');
    }

    /**
     * Get the default user settings.
     */
    protected function getDefaultSettings(): array
    {
        return [
            'dark_mode' => false,
            'language' => config('app.locale'),
            'theme' => 'default',
            'notification_preferences' => [
                'email_notifications' => true,
                'task_updates' => true,
                'team_updates' => true,
                'mention_notifications' => true,
                'due_date_reminders' => true,
                'assignment_notifications' => true,
            ],
        ];
    }
}
