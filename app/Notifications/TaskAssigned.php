<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    protected $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Task Assigned: {$this->task->title}")
            ->markdown('emails.tasks.assigned', [
                'task' => $this->task,
                'user' => $notifiable,
            ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'team_id' => $this->task->team_id,
            'team_name' => $this->task->team->name,
            'assigned_by' => $this->task->last_updated_by ?? $this->task->creator_id,
            'priority' => $this->task->priority,
            'due_date' => $this->task->due_date?->format('Y-m-d'),
            'type' => 'task_assigned',
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'team_id' => $this->task->team_id,
            'assigned_by' => $this->task->last_updated_by ?? $this->task->creator_id,
        ];
    }
}
