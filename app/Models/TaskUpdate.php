<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskUpdate extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'task_id',
        'user_id',
        'content',
        'ai_generated',
    ];

    protected $casts = [
        'ai_generated' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the task that owns the update.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the user who created the update.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the mentions in this update.
     */
    public function mentions(): HasMany
    {
        return $this->hasMany(TaskMention::class, 'update_id');
    }

    /**
     * Get mentioned users in this update.
     */
    public function mentionedUsers()
    {
        return $this->belongsToMany(User::class, 'task_mentions', 'update_id', 'mentioned_user_id');
    }

    /**
     * Parse content for mentions and create TaskMention records.
     * Looks for @username patterns in the content.
     */
    public function processMentions(): void
    {
        preg_match_all('/@([a-zA-Z0-9_]+)/', $this->content, $matches);
        
        if (!empty($matches[1])) {
            foreach ($matches[1] as $username) {
                $mentionedUser = User::where('username', $username)->first();
                
                if ($mentionedUser) {
                    TaskMention::create([
                        'update_id' => $this->id,
                        'mentioned_user_id' => $mentionedUser->id
                    ]);

                    // Create notification for mentioned user
                    Notification::create([
                        'user_id' => $mentionedUser->id,
                        'message' => "You were mentioned in a task update by {$this->user->username}",
                        'type' => 'mention',
                    ]);
                }
            }
        }
    }

    /**
     * Format the content with HTML for mentions.
     */
    public function getFormattedContent(): string
    {
        return preg_replace(
            '/@([a-zA-Z0-9_]+)/',
            '<a href="/users/$1" class="mention">@$1</a>',
            htmlspecialchars($this->content)
        );
    }

    /**
     * Check if the update was made by AI.
     */
    public function isAiGenerated(): bool
    {
        return $this->ai_generated;
    }

    /**
     * Get the time elapsed since the update was created.
     */
    public function getTimeElapsed(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($update) {
            // Process mentions when an update is created
            $update->processMentions();

            // Create activity log
            ActivityLog::create([
                'user_id' => $update->user_id,
                'activity' => 'Created task update',
                'details' => json_encode([
                    'task_id' => $update->task_id,
                    'update_id' => $update->id,
                ]),
            ]);
        });
    }
}
