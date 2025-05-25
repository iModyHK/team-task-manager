<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'team_id',
        'source',
        'meeting_title',
        'meeting_date',
        'task_name',
        'status_id',
        'priority_id',
        'label_id',
        'automation_rule_id',
        'due_date',
        'cco',
        'created_by',
        'archived_at',
    ];

    protected $casts = [
        'meeting_date' => 'date',
        'due_date' => 'date',
        'archived_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the team that owns the task.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the status of the task.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(TaskStatus::class, 'status_id');
    }

    /**
     * Get the priority of the task.
     */
    public function priority(): BelongsTo
    {
        return $this->belongsTo(TaskPriority::class, 'priority_id');
    }

    /**
     * Get the label of the task.
     */
    public function label(): BelongsTo
    {
        return $this->belongsTo(TaskLabel::class, 'label_id');
    }

    /**
     * Get the automation rule of the task.
     */
    public function automationRule(): BelongsTo
    {
        return $this->belongsTo(AutomationRule::class, 'automation_rule_id');
    }

    /**
     * Get the user who created the task.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the assignees of the task.
     */
    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_assignees')
            ->withTimestamps();
    }

    /**
     * Get the subtasks of the task.
     */
    public function subtasks(): HasMany
    {
        return $this->hasMany(Subtask::class, 'parent_task_id');
    }

    /**
     * Get the task's dependencies.
     */
    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(
            Task::class,
            'task_dependencies',
            'parent_task_id',
            'dependent_task_id'
        );
    }

    /**
     * Get the tasks that depend on this task.
     */
    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(
            Task::class,
            'task_dependencies',
            'dependent_task_id',
            'parent_task_id'
        );
    }

    /**
     * Get the task's attachments.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }

    /**
     * Get the task's updates.
     */
    public function updates(): HasMany
    {
        return $this->hasMany(TaskUpdate::class);
    }

    /**
     * Get the task's next steps.
     */
    public function nextSteps(): HasMany
    {
        return $this->hasMany(TaskNextStep::class);
    }

    /**
     * Get the task's PMO comments.
     */
    public function pmoComments(): HasMany
    {
        return $this->hasMany(TaskPmoComment::class);
    }

    /**
     * Get the task's custom field values.
     */
    public function customFieldValues(): HasMany
    {
        return $this->hasMany(TaskFieldValue::class);
    }

    /**
     * Get the task's reminders.
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(TaskReminder::class);
    }

    /**
     * Check if the task is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status->status_name === 'completed';
    }

    /**
     * Check if the task is archived.
     */
    public function isArchived(): bool
    {
        return !is_null($this->archived_at);
    }

    /**
     * Archive the task.
     */
    public function archive(): void
    {
        $this->update(['archived_at' => now()]);
    }

    /**
     * Unarchive the task.
     */
    public function unarchive(): void
    {
        $this->update(['archived_at' => null]);
    }

    /**
     * Scope a query to only include active tasks.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('archived_at')->whereNull('deleted_at');
    }

    /**
     * Scope a query to only include archived tasks.
     */
    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    /**
     * Scope a query to only include tasks for a specific team.
     */
    public function scopeForTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    /**
     * Scope a query to only include tasks assigned to a specific user.
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->whereHas('assignees', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }
}
