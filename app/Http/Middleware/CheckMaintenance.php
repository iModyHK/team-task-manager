<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;

class CheckMaintenance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if maintenance mode is enabled
        if (Setting::get('maintenance_mode', false)) {
            // Allow admins to bypass maintenance mode
            if ($request->user() && $request->user()->hasPermission('bypass_maintenance')) {
                // Log maintenance mode bypass
                audit_log('maintenance_mode_bypass', [
                    'user_id' => $request->user()->id,
                    'ip_address' => $request->ip(),
                    'attempted_url' => $request->url(),
                ]);

                return $next($request);
            }

            // Log maintenance mode access attempt
            audit_log('maintenance_mode_access', [
                'user_id' => $request->user()?->id,
                'ip_address' => $request->ip(),
                'attempted_url' => $request->url(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => Setting::get('maintenance_message', 'Application is currently in maintenance mode.'),
                ], 503);
            }

            // Show maintenance mode view
            return response()->view('errors.maintenance', [
                'message' => Setting::get('maintenance_message', 'Application is currently in maintenance mode.'),
                'estimated_time' => Setting::get('maintenance_end_time'),
            ], 503);
        }

        return $next($request);
    }
}
