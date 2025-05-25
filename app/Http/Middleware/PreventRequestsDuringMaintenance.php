<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;

class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * The URIs that should be accessible while maintenance mode is enabled.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Auth routes that should remain accessible
        'login',
        'logout',
        'password/*',
        
        // Admin routes for managing maintenance mode
        'admin/maintenance/*',
        
        // Health check endpoints
        'health',
        'health/*',
        
        // API status endpoint
        'api/status',
        
        // Asset routes
        'assets/*',
        'css/*',
        'js/*',
        'images/*',
        'fonts/*',
        
        // Additional routes that should remain accessible
        'maintenance',
        'maintenance/*',
    ];

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        if ($this->app->isDownForMaintenance()) {
            $data = json_decode(file_get_contents($this->app->storagePath().'/framework/down'), true);

            // Check if the current IP is allowed to bypass maintenance mode
            if (isset($data['allowed']) && is_array($data['allowed'])) {
                if (in_array($request->ip(), $data['allowed'])) {
                    return $next($request);
                }
            }

            // Check if the current path should be accessible
            $path = $request->path();
            foreach ($this->except as $except) {
                if ($except !== '/') {
                    $except = trim($except, '/');
                }

                if ($path === $except || str_starts_with($path.'/', $except.'/')) {
                    return $next($request);
                }
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $data['message'] ?? 'Application is down for maintenance.',
                    'retry_after' => isset($data['retry']) ? $data['retry'] - time() : null,
                ], 503);
            }

            // Log maintenance mode block
            if (function_exists('audit_log')) {
                audit_log('maintenance_mode_block', [
                    'ip_address' => $request->ip(),
                    'attempted_url' => $request->url(),
                    'user_agent' => $request->userAgent(),
                ]);
            }

            return response()->view('errors.maintenance', [
                'message' => $data['message'] ?? 'Application is down for maintenance.',
                'retry' => isset($data['retry']) ? $data['retry'] - time() : null,
            ], 503);
        }

        return $next($request);
    }

    /**
     * Get the URI that should be accessible during maintenance mode.
     *
     * @return array
     */
    public function getExcepts(): array
    {
        return $this->except;
    }

    /**
     * Add URIs that should be accessible during maintenance mode.
     *
     * @param  string|array  $paths
     * @return void
     */
    public function addExcepts($paths): void
    {
        $paths = is_array($paths) ? $paths : [$paths];
        $this->except = array_merge($this->except, $paths);
    }
}
