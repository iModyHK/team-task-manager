<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskPmoComment extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'task_id',
        'content',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the task that owns the PMO comment.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the formatted content with HTML.
     */
    public function getFormattedContent(): string
    {
        return nl2br(htmlspecialchars($this->content));
    }

    /**
     * Get the time elapsed since the comment was created.
     */
    public function getTimeElapsed(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Check if the authenticated user can edit this PMO comment.
     */
    public function canEdit(): bool
    {
        $user = auth()->user();
        return $user && ($user->hasPermission('manage_pmo_comments') || 
            $user->hasPermission('admin'));
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($pmoComment) {
            // Create activity log
            ActivityLog::create([
                'user_id' => auth()->id(),
                'activity' => 'Added PMO comment to task',
                'details' => json_encode([
                    'task_id' => $pmoComment->task_id,
                    'pmo_comment_id' => $pmoComment->id,
                ]),
            ]);

            // Create notifications for task assignees and team leader
            $task = $pmoComment->task;
            $notifyUsers = collect([$task->team->leader])
                ->merge($task->assignees)
                ->unique('id');

            foreach ($notifyUsers as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'message' => 'New PMO comment added to task',
                    'type' => 'pmo_comment',
                ]);
            }
        });

        static::updated(function ($pmoComment) {
            // Log PMO comment updates
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Updated PMO comment',
                'details' => json_encode([
                    'task_id' => $pmoComment->task_id,
                    'pmo_comment_id' => $pmoComment->id,
                    'changes' => $pmoComment->getChanges(),
                ]),
            ]);
        });

        static::deleted(function ($pmoComment) {
            // Log PMO comment deletions
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Deleted PMO comment',
                'details' => json_encode([
                    'task_id' => $pmoComment->task_id,
                    'pmo_comment_id' => $pmoComment->id,
                ]),
            ]);
        });
    }
}
