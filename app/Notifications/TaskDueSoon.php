<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskDueSoon extends Notification implements ShouldQueue
{
    use Queueable;

    protected $task;
    protected $daysUntilDue;

    public function __construct(Task $task, int $daysUntilDue)
    {
        $this->task = $task;
        $this->daysUntilDue = $daysUntilDue;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Task Due Soon: {$this->task->title}")
            ->markdown('emails.tasks.due-soon', [
                'task' => $this->task,
                'user' => $notifiable,
                'daysUntilDue' => $this->daysUntilDue
            ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'team_id' => $this->task->team_id,
            'team_name' => $this->task->team->name,
            'days_until_due' => $this->daysUntilDue,
            'due_date' => $this->task->due_date->format('Y-m-d'),
            'type' => 'task_due_soon',
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'days_until_due' => $this->daysUntilDue,
            'due_date' => $this->task->due_date->format('Y-m-d'),
        ];
    }
}
