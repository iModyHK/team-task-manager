<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\User;
use App\Models\Team;
use App\Models\Task;
use App\Models\Setting;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class AdminController extends Controller
{
    /**
     * Show the admin dashboard.
     */
    public function dashboard()
    {
        $this->authorize('access_admin_panel');

        // Get system statistics
        $stats = $this->getSystemStats();

        // Get recent activity
        $recentActivity = ActivityLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        // Get security events
        $securityEvents = AuditLog::with('user')
            ->where('action', 'like', '%login%')
            ->orWhere('action', 'like', '%password%')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentActivity', 'securityEvents'));
    }

    /**
     * Show the system settings page.
     */
    public function showSettings()
    {
        $this->authorize('manage_settings');

        $settings = Setting::getAllSettings();
        $timezones = \DateTimeZone::listIdentifiers();
        $languages = config('app.supported_languages');

        return view('admin.settings', compact('settings', 'timezones', 'languages'));
    }

    /**
     * Update system settings.
     */
    public function updateSettings(UpdateSettingsRequest $request)
    {
        try {
            DB::beginTransaction();

            $settings = $request->validated();

            // Update settings
            foreach ($settings as $key => $value) {
                Setting::updateSetting($key, $value);
            }

            // Clear settings cache
            Cache::forget('app_settings');

            // Log settings update
            audit_log('settings_updated', [
                'updated_by' => $request->user()->id,
                'changes' => $settings,
            ]);

            DB::commit();

            return back()->with('status', 'Settings updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors([
                'error' => 'An error occurred while updating settings.',
            ]);
        }
    }

    /**
     * Show the user management page.
     */
    public function users(Request $request)
    {
        $this->authorize('manage_users');

        $query = User::query()->with(['role', 'teams']);

        // Apply filters
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%");
            });
        }

        if ($request->has('role')) {
            $query->where('role_id', $request->role);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->paginate(20);

        return view('admin.users.index', [
            'users' => $users,
            'roles' => \App\Models\Role::all(),
        ]);
    }

    /**
     * Show the team management page.
     */
    public function teams(Request $request)
    {
        $this->authorize('manage_teams');

        $query = Team::query()
            ->with(['leader', 'members'])
            ->withCount(['members', 'tasks']);

        // Apply filters
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('leader')) {
            $query->where('leader_id', $request->leader);
        }

        $teams = $query->paginate(20);

        return view('admin.teams.index', [
            'teams' => $teams,
            'leaders' => User::whereHas('ledTeams')->get(),
        ]);
    }

    /**
     * Show the task management page.
     */
    public function tasks(Request $request)
    {
        $this->authorize('manage_tasks');

        $query = Task::query()
            ->with(['team', 'status', 'priority', 'creator', 'assignees']);

        // Apply filters
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('task_name', 'like', "%{$search}%")
                    ->orWhere('source', 'like', "%{$search}%");
            });
        }

        if ($request->has('team')) {
            $query->where('team_id', $request->team);
        }

        if ($request->has('status')) {
            $query->where('status_id', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority_id', $request->priority);
        }

        $tasks = $query->paginate(20);

        return view('admin.tasks.index', [
            'tasks' => $tasks,
            'teams' => Team::all(),
            'statuses' => \App\Models\TaskStatus::all(),
            'priorities' => \App\Models\TaskPriority::all(),
        ]);
    }

    /**
     * Show the activity log page.
     */
    public function activityLog(Request $request)
    {
        $this->authorize('view_activity_log');

        $query = ActivityLog::query()->with('user');

        // Apply filters
        if ($request->has('user')) {
            $query->where('user_id', $request->user);
        }

        if ($request->has('activity')) {
            $query->where('activity', $request->activity);
        }

        if ($request->has('date_range')) {
            $dates = explode(' - ', $request->date_range);
            $query->whereBetween('created_at', $dates);
        }

        $logs = $query->latest()->paginate(50);

        return view('admin.logs.activity', [
            'logs' => $logs,
            'users' => User::all(),
        ]);
    }

    /**
     * Show the audit log page.
     */
    public function auditLog(Request $request)
    {
        $this->authorize('view_audit_log');

        $query = AuditLog::query()->with('user');

        // Apply filters
        if ($request->has('user')) {
            $query->where('user_id', $request->user);
        }

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        if ($request->has('date_range')) {
            $dates = explode(' - ', $request->date_range);
            $query->whereBetween('created_at', $dates);
        }

        $logs = $query->latest()->paginate(50);

        return view('admin.logs.audit', [
            'logs' => $logs,
            'users' => User::all(),
        ]);
    }

    /**
     * Clear system cache.
     */
    public function clearCache(Request $request)
    {
        $this->authorize('manage_settings');

        try {
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('config:clear');

            // Log cache clear
            audit_log('cache_cleared', [
                'cleared_by' => $request->user()->id,
            ]);

            return back()->with('status', 'System cache cleared successfully.');

        } catch (\Exception $e) {
            report($e);
            return back()->withErrors([
                'error' => 'An error occurred while clearing the cache.',
            ]);
        }
    }

    /**
     * Get system statistics.
     */
    protected function getSystemStats(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'total_teams' => Team::count(),
            'total_tasks' => Task::count(),
            'completed_tasks' => Task::whereHas('status', function ($query) {
                $query->where('status_name', 'completed');
            })->count(),
            'overdue_tasks' => Task::where('due_date', '<', now())
                ->whereHas('status', function ($query) {
                    $query->whereNotIn('status_name', ['completed']);
                })
                ->count(),
            'disk_usage' => $this->getDiskUsage(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_load' => sys_getloadavg(),
        ];
    }

    /**
     * Get disk usage statistics.
     */
    protected function getDiskUsage(): array
    {
        $totalSpace = disk_total_space(base_path());
        $freeSpace = disk_free_space(base_path());
        $usedSpace = $totalSpace - $freeSpace;

        return [
            'total' => $this->formatBytes($totalSpace),
            'used' => $this->formatBytes($usedSpace),
            'free' => $this->formatBytes($freeSpace),
            'percentage' => round(($usedSpace / $totalSpace) * 100, 2),
        ];
    }

    /**
     * Format bytes to human readable format.
     */
    protected function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
