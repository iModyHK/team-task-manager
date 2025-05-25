<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskMention extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'update_id',
        'mentioned_user_id',
    ];

    /**
     * Get the update that contains this mention.
     */
    public function update(): BelongsTo
    {
        return $this->belongsTo(TaskUpdate::class, 'update_id');
    }

    /**
     * Get the mentioned user.
     */
    public function mentionedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentioned_user_id');
    }

    /**
     * Get the task associated with this mention.
     */
    public function task()
    {
        return $this->update->task;
    }

    /**
     * Check if the mention has been read by the mentioned user.
     */
    public function isRead(): bool
    {
        return Notification::where([
            'user_id' => $this->mentioned_user_id,
            'type' => 'mention',
        ])
        ->whereJsonContains('data->mention_id', $this->id)
        ->where('read_at', '!=', null)
        ->exists();
    }

    /**
     * Mark the mention as read.
     */
    public function markAsRead(): void
    {
        Notification::where([
            'user_id' => $this->mentioned_user_id,
            'type' => 'mention',
        ])
        ->whereJsonContains('data->mention_id', $this->id)
        ->update(['read_at' => now()]);
    }

    /**
     * Get the URL to the mentioned context.
     */
    public function getContextUrl(): string
    {
        return route('tasks.show', [
            'task' => $this->task->id,
            'highlight' => $this->update->id
        ]);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($mention) {
            // Create activity log
            ActivityLog::create([
                'user_id' => $mention->update->user_id,
                'activity' => 'Mentioned user in task update',
                'details' => json_encode([
                    'task_id' => $mention->task()->id,
                    'update_id' => $mention->update_id,
                    'mentioned_user_id' => $mention->mentioned_user_id,
                ]),
            ]);
        });
    }
}
