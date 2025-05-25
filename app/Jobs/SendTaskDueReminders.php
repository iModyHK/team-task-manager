<?php

namespace App\Jobs;

use App\Models\Task;
use App\Notifications\TaskDueSoon;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTaskDueReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = [60, 300, 3600];

    public function handle()
    {
        // Get tasks due in the next week that haven't been completed
        $tasks = Task::where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->where('due_date', '>', Carbon::now())
            ->where('due_date', '<=', Carbon::now()->addWeek())
            ->whereDoesntHave('notifications', function ($query) {
                $query->where('created_at', '>=', Carbon::now()->subDay());
            })
            ->with(['assignee', 'team'])
            ->get();

        foreach ($tasks as $task) {
            $daysUntilDue = Carbon::now()->diffInDays($task->due_date, false);

            // Send reminders based on how soon the task is due
            if ($daysUntilDue <= 1) {
                // Due within 24 hours
                $this->sendReminder($task, $daysUntilDue);
            } elseif ($daysUntilDue <= 3) {
                // Due within 3 days
                $this->sendReminder($task, $daysUntilDue);
            } elseif ($daysUntilDue <= 7) {
                // Due within a week
                $this->sendReminder($task, $daysUntilDue);
            }
        }
    }

    protected function sendReminder(Task $task, int $daysUntilDue)
    {
        if ($task->assignee) {
            $task->assignee->notify(new TaskDueSoon($task, $daysUntilDue));
        }

        // Also notify team leads or watchers if configured
        if ($task->watchers) {
            foreach ($task->watchers as $watcher) {
                $watcher->notify(new TaskDueSoon($task, $daysUntilDue));
            }
        }

        // Record that we sent a reminder
        $task->notifications()->create([
            'type' => 'due_reminder',
            'sent_at' => now(),
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        // Log the failure
        \Log::error('Failed to send task due reminders', [
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
