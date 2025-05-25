<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Setting;
use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            // Authenticated user data
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'full_name' => $user->full_name,
                    'avatar' => $user->avatar_url,
                    'role' => $user->role?->name,
                    'permissions' => $user->getAllPermissions(),
                    'settings' => $user->settings,
                    'unread_notifications_count' => $user->unreadNotifications()->count(),
                ] : null,
            ],

            // Flash messages
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
                'warning' => session('warning'),
                'info' => session('info'),
                'status' => session('status'),
            ],

            // Application settings
            'app' => [
                'name' => Setting::get('app_name', config('app.name')),
                'description' => Setting::get('app_description'),
                'logo' => Setting::get('app_logo'),
                'theme' => Setting::get('app_theme', 'light'),
                'language' => app()->getLocale(),
                'timezone' => config('app.timezone'),
                'debug' => config('app.debug'),
                'version' => config('app.version'),
            ],

            // Routes via Ziggy
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },

            // System notifications
            'system' => [
                'maintenance_mode' => Setting::get('maintenance_mode', false),
                'maintenance_message' => Setting::get('maintenance_message'),
                'announcements' => $this->getActiveAnnouncements(),
            ],

            // CSRF token
            'csrf_token' => csrf_token(),
        ]);
    }

    /**
     * Get active system announcements.
     */
    protected function getActiveAnnouncements(): array
    {
        try {
            return \App\Models\Announcement::active()
                ->orderByDesc('priority')
                ->limit(5)
                ->get()
                ->map(function ($announcement) {
                    return [
                        'id' => $announcement->id,
                        'title' => $announcement->title,
                        'message' => $announcement->message,
                        'type' => $announcement->type,
                        'starts_at' => $announcement->starts_at?->toDateTimeString(),
                        'ends_at' => $announcement->ends_at?->toDateTimeString(),
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            report($e);
            return [];
        }
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request, \Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (\Exception $e) {
            if (!app()->environment('production')) {
                throw $e;
            }

            report($e);

            if ($request->header('X-Inertia')) {
                return response()->json([
                    'message' => 'An error occurred while processing your request.',
                ], 500);
            }

            return redirect()->back()->with('error', 'An error occurred while processing your request.');
        }
    }
}
