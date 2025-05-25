<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
*/

// Maintenance and cleanup commands
Artisan::command('tasks:clean-old', function () {
    $this->info('Cleaning up old completed tasks...');
    
    // Archive tasks completed more than 6 months ago
    $count = Task::where('status', 'completed')
        ->where('completed_at', '<', now()->subMonths(6))
        ->update(['archived' => true]);
    
    $this->info("Archived {$count} old completed tasks.");
})->purpose('Archive old completed tasks');

Artisan::command('teams:clean-invitations', function () {
    $this->info('Cleaning up expired team invitations...');
    
    // Delete invitations older than the configured expiry time
    $count = TeamInvitation::where('created_at', '<', now()->subDays(config('auth.invitation_expires_in', 7)))
        ->delete();
    
    $this->info("Deleted {$count} expired team invitations.");
})->purpose('Remove expired team invitations');

// Notification commands
Artisan::command('notifications:clean', function () {
    $this->info('Cleaning up old notifications...');
    
    // Delete read notifications older than 3 months
    $count = DatabaseNotification::whereNotNull('read_at')
        ->where('created_at', '<', now()->subMonths(3))
        ->delete();
    
    $this->info("Deleted {$count} old notifications.");
})->purpose('Remove old read notifications');

// Statistics and reporting commands
Artisan::command('stats:generate-monthly', function () {
    $this->info('Generating monthly statistics...');
    
    $date = now()->subMonth();
    $year = $date->year;
    $month = $date->month;
    
    // Tasks statistics
    $tasksStats = [
        'total_created' => Task::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count(),
        'total_completed' => Task::whereYear('completed_at', $year)
            ->whereMonth('completed_at', $month)
            ->where('status', 'completed')
            ->count(),
        'avg_completion_time' => Task::whereYear('completed_at', $year)
            ->whereMonth('completed_at', $month)
            ->where('status', 'completed')
            ->avg(DB::raw('TIMESTAMPDIFF(HOUR, created_at, completed_at)'))
    ];
    
    // Store statistics in database or generate report
    MonthlyStats::create([
        'year' => $year,
        'month' => $month,
        'data' => $tasksStats
    ]);
    
    $this->info('Monthly statistics generated successfully.');
})->purpose('Generate monthly statistics report');

// System health check commands
Artisan::command('system:health-check', function () {
    $this->info('Running system health check...');
    
    // Check database connection
    try {
        DB::connection()->getPdo();
        $this->info('✓ Database connection: OK');
    } catch (\Exception $e) {
        $this->error('✗ Database connection: FAILED');
        $this->error($e->getMessage());
    }
    
    // Check Redis connection
    try {
        Redis::ping();
        $this->info('✓ Redis connection: OK');
    } catch (\Exception $e) {
        $this->error('✗ Redis connection: FAILED');
        $this->error($e->getMessage());
    }
    
    // Check storage permissions
    if (is_writable(storage_path()) && is_writable(storage_path('logs'))) {
        $this->info('✓ Storage permissions: OK');
    } else {
        $this->error('✗ Storage permissions: FAILED');
    }
    
    // Check queue worker status
    $queueCount = Queue::size();
    $this->info("Queue size: {$queueCount} jobs");
    
    // Check disk space
    $diskSpace = disk_free_space('/') / disk_total_space('/') * 100;
    $this->info("Disk space available: {$diskSpace}%");
    
})->purpose('Check system health status');

// Development helper commands
if (app()->environment('local')) {
    Artisan::command('dev:seed-test-data', function () {
        $this->info('Seeding test data...');
        
        // Create test users
        User::factory()->count(10)->create();
        
        // Create test teams
        Team::factory()
            ->count(3)
            ->hasAttached(User::all()->random(5))
            ->create();
        
        // Create test tasks
        Task::factory()
            ->count(50)
            ->create();
        
        $this->info('Test data seeded successfully.');
    })->purpose('Seed test data for development');
}
