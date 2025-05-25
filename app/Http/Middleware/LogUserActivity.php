<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\UserSession;
use Carbon\Carbon;

class LogUserActivity
{
    /**
     * Routes that should not be logged.
     *
     * @var array<string>
     */
    protected $excludedRoutes = [
        'health',
        'health/*',
        '_debugbar/*',
        'sanctum/csrf-cookie',
        'livewire/*',
        'assets/*',
        'js/*',
        'css/*',
        'images/*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Process the request
        $response = $next($request);

        // Only log activity for authenticated users
        if ($request->user() && !$this->shouldExclude($request)) {
            $this->logActivity($request);
        }

        return $response;
    }

    /**
     * Log the user activity.
     */
    protected function logActivity(Request $request): void
    {
        try {
            $user = $request->user();
            $now = Carbon::now();

            // Update or create user session
            $session = UserSession::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'session_token' => session()->getId(),
                ],
                [
                    'last_active' => $now,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'last_page' => $request->fullUrl(),
                ]
            );

            // Update user's last activity timestamp
            $user->update([
                'last_active_at' => $now,
            ]);

            // Log specific actions
            if ($this->isSignificantAction($request)) {
                activity_log('user_action', [
                    'user_id' => $user->id,
                    'action' => $this->getActionName($request),
                    'route' => $request->route()->getName(),
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }

        } catch (\Exception $e) {
            report($e);
        }
    }

    /**
     * Determine if the request should be excluded from logging.
     */
    protected function shouldExclude(Request $request): bool
    {
        $path = $request->path();

        // Check excluded routes
        foreach ($this->excludedRoutes as $route) {
            if ($route !== '/') {
                $route = trim($route, '/');
            }

            if ($path === $route || str_starts_with($path.'/', $route.'/')) {
                return true;
            }
        }

        // Don't log asset requests
        if ($request->is('*.js', '*.css', '*.jpg', '*.png', '*.gif', '*.ico')) {
            return true;
        }

        // Don't log AJAX polling requests
        if ($request->ajax() && $request->is('notifications/check', 'activities/check')) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the action is significant enough to log.
     */
    protected function isSignificantAction(Request $request): bool
    {
        // Log all non-GET requests
        if (!$request->isMethod('GET')) {
            return true;
        }

        // Log specific GET requests
        $significantRoutes = [
            'profile.*',
            'settings.*',
            'admin.*',
            'teams.*',
            'tasks.*',
        ];

        $routeName = $request->route()->getName();
        
        foreach ($significantRoutes as $route) {
            if (str_is($route, $routeName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get a human-readable name for the action.
     */
    protected function getActionName(Request $request): string
    {
        $routeName = $request->route()->getName();
        $method = $request->method();

        // Convert route name to action description
        $action = str_replace('.', ' ', $routeName);
        $action = str_replace('_', ' ', $action);
        $action = ucwords($action);

        // Add method for non-GET requests
        if ($method !== 'GET') {
            $action = strtoupper($method) . ' ' . $action;
        }

        return $action;
    }

    /**
     * Add routes to the exclusion list.
     */
    public function addExcludedRoutes(array $routes): void
    {
        $this->excludedRoutes = array_merge($this->excludedRoutes, $routes);
    }

    /**
     * Get the list of excluded routes.
     */
    public function getExcludedRoutes(): array
    {
        return $this->excludedRoutes;
    }
}
