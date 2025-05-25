<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomationRule extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'rule_name',
        'trigger_event',
        'action',
    ];

    protected $casts = [
        'trigger_event' => 'array',
        'action' => 'array',
    ];

    /**
     * Get the tasks using this automation rule.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'automation_rule_id');
    }

    /**
     * Execute the automation rule on a task.
     */
    public function execute(Task $task): bool
    {
        try {
            $triggerEvent = $this->trigger_event;
            $action = $this->action;

            // Check if trigger conditions are met
            if ($this->checkTriggerConditions($task, $triggerEvent)) {
                // Execute the automation action
                return $this->executeAction($task, $action);
            }

            return false;
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Automation rule execution failed: ' . $e->getMessage(), [
                'rule_id' => $this->id,
                'task_id' => $task->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if trigger conditions are met.
     */
    protected function checkTriggerConditions(Task $task, array $triggerEvent): bool
    {
        // Example trigger conditions:
        // - Task status changes to X
        // - Due date is approaching (N days)
        // - No activity for N days
        // - Priority changes to X
        
        switch ($triggerEvent['type']) {
            case 'status_change':
                return $task->status_id === $triggerEvent['status_id'];
            
            case 'due_date_approaching':
                $daysUntilDue = now()->diffInDays($task->due_date, false);
                return $daysUntilDue <= $triggerEvent['days'];
            
            case 'no_activity':
                $lastActivity = $task->updates()->latest()->first();
                if (!$lastActivity) return true;
                return now()->diffInDays($lastActivity->created_at) >= $triggerEvent['days'];
            
            case 'priority_change':
                return $task->priority_id === $triggerEvent['priority_id'];
            
            default:
                return false;
        }
    }

    /**
     * Execute the automation action.
     */
    protected function executeAction(Task $task, array $action): bool
    {
        // Example actions:
        // - Change status
        // - Assign to user
        // - Send notification
        // - Create reminder
        
        switch ($action['type']) {
            case 'change_status':
                return $task->update(['status_id' => $action['status_id']]);
            
            case 'assign_user':
                return $task->assignees()->sync([$action['user_id']], false);
            
            case 'send_notification':
                $user = User::find($action['user_id']);
                if ($user) {
                    Notification::create([
                        'user_id' => $user->id,
                        'message' => $action['message'],
                        'type' => 'automation',
                    ]);
                    return true;
                }
                return false;
            
            case 'create_reminder':
                TaskReminder::create([
                    'task_id' => $task->id,
                    'user_id' => $action['user_id'],
                    'remind_at' => now()->addDays($action['days']),
                ]);
                return true;
            
            default:
                return false;
        }
    }

    /**
     * Get example automation rules.
     */
    public static function getDefaultRules(): array
    {
        return [
            [
                'rule_name' => 'Due Date Reminder',
                'trigger_event' => [
                    'type' => 'due_date_approaching',
                    'days' => 2
                ],
                'action' => [
                    'type' => 'send_notification',
                    'message' => 'Task is due in 2 days'
                ]
            ],
            [
                'rule_name' => 'Auto Assign on High Priority',
                'trigger_event' => [
                    'type' => 'priority_change',
                    'priority' => 'high'
                ],
                'action' => [
                    'type' => 'assign_user',
                    'role' => 'team_lead'
                ]
            ],
        ];
    }
}
