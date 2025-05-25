<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get user's teams
        $teams = $user->teams()
            ->with('leader')
            ->withCount(['tasks', 'members'])
            ->get();

        // Get tasks statistics
        $taskStats = $this->getTaskStatistics($user);

        // Get recent activities
        $recentActivities = $user->activityLogs()
            ->with('user')
            ->latest()
            ->take(10)
            ->get();

        // Get upcoming tasks
        $upcomingTasks = Task::query()
            ->whereHas('assignees', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays(7))
            ->whereNull('archived_at')
            ->orderBy('due_date')
            ->take(5)
            ->get();

        // Get team performance metrics
        $teamMetrics = $this->getTeamMetrics($user);

        // Get task completion trends
        $completionTrends = $this->getCompletionTrends($user);

        return view('dashboard', compact(
            'teams',
            'taskStats',
            'recentActivities',
            'upcomingTasks',
            'teamMetrics',
            'completionTrends'
        ));
    }

    /**
     * Get task statistics for the user.
     */
    protected function getTaskStatistics(User $user): array
    {
        $assignedTasks = Task::query()
            ->whereHas('assignees', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereNull('archived_at');

        $createdTasks = Task::query()
            ->where('created_by', $user->id)
            ->whereNull('archived_at');

        return [
            'total_assigned' => $assignedTasks->count(),
            'pending_tasks' => $assignedTasks->whereHas('status', function ($query) {
                $query->where('status_name', 'pending');
            })->count(),
            'in_progress_tasks' => $assignedTasks->whereHas('status', function ($query) {
                $query->where('status_name', 'in_progress');
            })->count(),
            'completed_tasks' => $assignedTasks->whereHas('status', function ($query) {
                $query->where('status_name', 'completed');
            })->count(),
            'overdue_tasks' => $assignedTasks
                ->where('due_date', '<', now())
                ->whereHas('status', function ($query) {
                    $query->whereNotIn('status_name', ['completed']);
                })
                ->count(),
            'created_tasks' => $createdTasks->count(),
            'completion_rate' => $this->calculateCompletionRate($user),
        ];
    }

    /**
     * Calculate task completion rate for the user.
     */
    protected function calculateCompletionRate(User $user): float
    {
        $totalTasks = Task::query()
            ->whereHas('assignees', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereNull('archived_at')
            ->count();

        if ($totalTasks === 0) {
            return 0;
        }

        $completedTasks = Task::query()
            ->whereHas('assignees', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereHas('status', function ($query) {
                $query->where('status_name', 'completed');
            })
            ->whereNull('archived_at')
            ->count();

        return round(($completedTasks / $totalTasks) * 100, 2);
    }

    /**
     * Get team performance metrics.
     */
    protected function getTeamMetrics(User $user): array
    {
        $teams = $user->teams()->with(['tasks', 'members'])->get();

        return $teams->map(function ($team) {
            $totalTasks = $team->tasks->count();
            $completedTasks = $team->tasks->filter(function ($task) {
                return $task->status->status_name === 'completed';
            })->count();

            return [
                'team_id' => $team->id,
                'team_name' => $team->name,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'completion_rate' => $totalTasks > 0 
                    ? round(($completedTasks / $totalTasks) * 100, 2) 
                    : 0,
                'member_count' => $team->members->count(),
                'active_tasks' => $team->tasks->whereNull('archived_at')->count(),
            ];
        })->toArray();
    }

    /**
     * Get task completion trends over time.
     */
    protected function getCompletionTrends(User $user): array
    {
        $trends = [];
        $startDate = now()->subDays(30);

        // Get completed tasks by day
        $completedTasks = Task::query()
            ->whereHas('assignees', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereHas('status', function ($query) {
                $query->where('status_name', 'completed');
            })
            ->where('updated_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(updated_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Format data for chart
        foreach ($completedTasks as $record) {
            $trends[$record->date] = $record->count;
        }

        // Fill in missing dates with zero
        $date = $startDate->copy();
        while ($date <= now()) {
            $dateStr = $date->format('Y-m-d');
            if (!isset($trends[$dateStr])) {
                $trends[$dateStr] = 0;
            }
            $date->addDay();
        }

        ksort($trends);

        return $trends;
    }
}
