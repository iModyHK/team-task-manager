<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TaskTemplate extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'template_name',
        'default_status_id',
        'default_due_days',
    ];

    protected $casts = [
        'default_due_days' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the default status for this template.
     */
    public function defaultStatus(): BelongsTo
    {
        return $this->belongsTo(TaskStatus::class, 'default_status_id');
    }

    /**
     * Create a new task from this template.
     */
    public function createTask(array $attributes = []): Task
    {
        $dueDate = isset($this->default_due_days) 
            ? Carbon::now()->addDays($this->default_due_days)
            : null;

        $taskData = array_merge([
            'status_id' => $this->default_status_id,
            'due_date' => $dueDate,
            'created_by' => auth()->id(),
        ], $attributes);

        $task = Task::create($taskData);

        // Log the task creation from template
        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity' => 'Created task from template',
            'details' => json_encode([
                'task_id' => $task->id,
                'template_id' => $this->id,
            ]),
        ]);

        return $task;
    }

    /**
     * Get default task templates.
     */
    public static function getDefaultTemplates(): array
    {
        return [
            [
                'template_name' => 'Bug Report',
                'default_due_days' => 3,
                'subtasks' => [
                    'Reproduce the issue',
                    'Identify root cause',
                    'Implement fix',
                    'Write tests',
                    'Code review',
                    'Deploy to staging',
                    'Verify fix',
                ],
            ],
            [
                'template_name' => 'Feature Request',
                'default_due_days' => 7,
                'subtasks' => [
                    'Requirements gathering',
                    'Design documentation',
                    'Implementation',
                    'Unit tests',
                    'Integration tests',
                    'Code review',
                    'User acceptance testing',
                ],
            ],
            [
                'template_name' => 'Documentation Update',
                'default_due_days' => 2,
                'subtasks' => [
                    'Review current documentation',
                    'Identify gaps',
                    'Update documentation',
                    'Peer review',
                    'Publish changes',
                ],
            ],
        ];
    }

    /**
     * Create subtasks for a task based on template.
     */
    public function createSubtasksForTask(Task $task, array $subtaskTitles): void
    {
        foreach ($subtaskTitles as $title) {
            Subtask::create([
                'parent_task_id' => $task->id,
                'title' => $title,
                'status' => 'pending',
            ]);
        }
    }

    /**
     * Clone an existing task as a template.
     */
    public static function createFromTask(Task $task, string $templateName): self
    {
        $template = self::create([
            'template_name' => $templateName,
            'default_status_id' => $task->status_id,
            'default_due_days' => $task->due_date 
                ? Carbon::now()->diffInDays($task->due_date) 
                : null,
        ]);

        // Log template creation
        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity' => 'Created template from task',
            'details' => json_encode([
                'template_id' => $template->id,
                'source_task_id' => $task->id,
            ]),
        ]);

        return $template;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($template) {
            // Log template creation
            ActivityLog::create([
                'user_id' => auth()->id(),
                'activity' => 'Created task template',
                'details' => json_encode([
                    'template_id' => $template->id,
                ]),
            ]);
        });

        static::updated(function ($template) {
            // Log template updates
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Updated task template',
                'details' => json_encode([
                    'template_id' => $template->id,
                    'changes' => $template->getChanges(),
                ]),
            ]);
        });
    }
}
