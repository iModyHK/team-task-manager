<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * Show the user's profile.
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $settings = $user->settings;
        $activities = $user->activityLogs()
            ->latest()
            ->take(10)
            ->get();

        return view('profile.show', [
            'user' => $user,
            'settings' => $settings,
            'activities' => $activities,
            'sessions' => $user->sessions()->latest()->get(),
        ]);
    }

    /**
     * Update the user's profile.
     */
    public function update(UpdateProfileRequest $request)
    {
        try {
            $user = $request->user();
            $data = $request->validated();

            // Handle password update
            if (isset($data['new_password'])) {
                $data['password'] = Hash::make($data['new_password']);
                unset($data['new_password'], $data['current_password']);

                // Log password change
                audit_log('password_changed', [
                    'user_id' => $user->id,
                    'ip_address' => $request->ip(),
                ]);
            }

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                $path = $request->file('avatar')->store('avatars', 'public');
                
                // Delete old avatar if exists
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }
                
                $data['avatar'] = $path;
            }

            // Check if email is being changed
            $emailChanged = $user->email !== $data['email'];
            if ($emailChanged) {
                $data['email_verified'] = false;
                
                // Log email change
                audit_log('email_changed', [
                    'user_id' => $user->id,
                    'old_email' => $user->email,
                    'new_email' => $data['email'],
                    'ip_address' => $request->ip(),
                ]);
            }

            // Update user profile
            $user->update($data);

            // Update user settings
            $user->settings()->update([
                'language' => $data['language'],
                'timezone' => $data['timezone'],
            ]);

            // Log profile update
            activity_log('profile_updated', [
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
            ]);

            // Send verification email if email changed
            if ($emailChanged) {
                $user->sendEmailVerificationNotification();
            }

            return back()->with('status', 'Profile updated successfully.');

        } catch (\Exception $e) {
            report($e);
            return back()->withErrors([
                'error' => 'An error occurred while updating your profile.',
            ]);
        }
    }

    /**
     * Update the user's notification preferences.
     */
    public function updateNotifications(Request $request)
    {
        $request->validate([
            'preferences' => ['required', 'array'],
            'preferences.*' => ['boolean'],
        ]);

        try {
            $user = $request->user();
            
            $user->settings()->update([
                'notification_preferences' => $request->preferences,
            ]);

            // Log notification preferences update
            activity_log('notification_preferences_updated', [
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
            ]);

            return back()->with('status', 'Notification preferences updated successfully.');

        } catch (\Exception $e) {
            report($e);
            return back()->withErrors([
                'error' => 'An error occurred while updating notification preferences.',
            ]);
        }
    }

    /**
     * Update the user's theme preferences.
     */
    public function updateTheme(Request $request)
    {
        $request->validate([
            'dark_mode' => ['required', 'boolean'],
            'theme' => ['required', 'string', 'in:default,modern,classic'],
        ]);

        try {
            $user = $request->user();
            
            $user->settings()->update([
                'dark_mode' => $request->dark_mode,
                'theme' => $request->theme,
            ]);

            // Log theme preferences update
            activity_log('theme_preferences_updated', [
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
            ]);

            return back()->with('status', 'Theme preferences updated successfully.');

        } catch (\Exception $e) {
            report($e);
            return back()->withErrors([
                'error' => 'An error occurred while updating theme preferences.',
            ]);
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);

        try {
            $user = $request->user();

            // Log account deletion
            audit_log('account_deleted', [
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
            ]);

            // Delete avatar
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Logout and delete user
            Auth::logout();
            $user->delete();

            return redirect()->route('login')
                ->with('status', 'Your account has been deleted successfully.');

        } catch (\Exception $e) {
            report($e);
            return back()->withErrors([
                'error' => 'An error occurred while deleting your account.',
            ]);
        }
    }

    /**
     * Show the user's active sessions.
     */
    public function showSessions(Request $request)
    {
        $sessions = $request->user()
            ->sessions()
            ->latest('last_active')
            ->get();

        return view('profile.sessions', [
            'sessions' => $sessions,
        ]);
    }

    /**
     * Revoke a specific session.
     */
    public function revokeSession(Request $request, string $sessionId)
    {
        try {
            $session = $request->user()
                ->sessions()
                ->findOrFail($sessionId);

            $session->delete();

            // Log session revocation
            audit_log('session_revoked', [
                'user_id' => $request->user()->id,
                'session_id' => $sessionId,
                'ip_address' => $request->ip(),
            ]);

            return back()->with('status', 'Session revoked successfully.');

        } catch (\Exception $e) {
            report($e);
            return back()->withErrors([
                'error' => 'An error occurred while revoking the session.',
            ]);
        }
    }

    /**
     * Revoke all sessions except the current one.
     */
    public function revokeOtherSessions(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);

        try {
            $currentSession = $request->user()
                ->sessions()
                ->where('ip_address', $request->ip())
                ->where('user_agent', $request->userAgent())
                ->first();

            $request->user()
                ->sessions()
                ->where('id', '!=', $currentSession->id)
                ->delete();

            // Log other sessions revocation
            audit_log('other_sessions_revoked', [
                'user_id' => $request->user()->id,
                'ip_address' => $request->ip(),
            ]);

            return back()->with('status', 'All other sessions have been revoked.');

        } catch (\Exception $e) {
            report($e);
            return back()->withErrors([
                'error' => 'An error occurred while revoking other sessions.',
            ]);
        }
    }
}
