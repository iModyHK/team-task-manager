<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subtask extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'parent_task_id',
        'title',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the parent task.
     */
    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    /**
     * Check if the subtask is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the subtask is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Mark the subtask as completed.
     */
    public function complete(): void
    {
        $this->update(['status' => 'completed']);
    }

    /**
     * Mark the subtask as in progress.
     */
    public function startProgress(): void
    {
        $this->update(['status' => 'in_progress']);
    }

    /**
     * Reset the subtask to pending status.
     */
    public function reset(): void
    {
        $this->update(['status' => 'pending']);
    }

    /**
     * Get the status badge HTML.
     */
    public function getStatusBadge(): string
    {
        $colors = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'in_progress' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-green-100 text-green-800',
        ];

        $color = $colors[$this->status] ?? 'bg-gray-100 text-gray-800';

        return sprintf(
            '<span class="px-2 py-1 text-xs font-medium rounded-full %s">%s</span>',
            $color,
            ucfirst(str_replace('_', ' ', $this->status))
        );
    }

    /**
     * Get the progress percentage of all subtasks for a parent task.
     */
    public static function getProgressForTask(Task $task): int
    {
        $total = $task->subtasks()->count();
        if ($total === 0) return 0;

        $completed = $task->subtasks()
            ->where('status', 'completed')
            ->count();

        return (int) (($completed / $total) * 100);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($subtask) {
            // Create activity log
            ActivityLog::create([
                'user_id' => auth()->id(),
                'activity' => 'Created subtask',
                'details' => json_encode([
                    'task_id' => $subtask->parent_task_id,
                    'subtask_id' => $subtask->id,
                ]),
            ]);
        });

        static::updated(function ($subtask) {
            if ($subtask->isDirty('status')) {
                // Log status changes
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'activity' => 'Updated subtask status',
                    'details' => json_encode([
                        'task_id' => $subtask->parent_task_id,
                        'subtask_id' => $subtask->id,
                        'old_status' => $subtask->getOriginal('status'),
                        'new_status' => $subtask->status,
                    ]),
                ]);

                // Create notification for task owner and assignees
                $task = $subtask->parentTask;
                $notifyUsers = collect([$task->creator])
                    ->merge($task->assignees)
                    ->unique('id');

                foreach ($notifyUsers as $user) {
                    Notification::create([
                        'user_id' => $user->id,
                        'message' => "Subtask status updated to {$subtask->status}",
                        'type' => 'subtask_update',
                    ]);
                }
            }
        });
    }
}
