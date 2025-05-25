<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Send task reminders daily at 8 AM
        $schedule->command('tasks:send-reminders')
                ->dailyAt('08:00')
                ->timezone('UTC')
                ->onOneServer();

        // Clean up expired team invitations weekly
        $schedule->command('teams:clean-invitations')
                ->weekly()
                ->sundays()
                ->at('00:00')
                ->onOneServer();

        // Prune old notification records monthly
        $schedule->command('notifications:prune')
                ->monthly()
                ->onOneServer();

        // Monitor queue health
        $schedule->command('queue:monitor')
                ->everyFiveMinutes();

        // Restart queue workers daily to prevent memory leaks
        $schedule->command('queue:restart')
                ->daily();

        // Clean up failed jobs weekly
        $schedule->command('queue:prune-failed')
                ->weekly()
                ->onOneServer();

        // Clean up old activity logs monthly
        $schedule->command('activitylog:clean')
                ->monthly()
                ->onOneServer();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
