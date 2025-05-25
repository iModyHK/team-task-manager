<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $task = $this->route('task');
        $user = $this->user();

        return $user->hasPermission('edit_tasks') || 
            ($task && ($task->created_by === $user->id || $task->assignees->contains($user)));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $task = $this->route('task');

        return [
            'team_id' => [
                'sometimes',
                'uuid',
                Rule::exists('teams', 'id'),
                function ($attribute, $value, $fail) {
                    $team = \App\Models\Team::find($value);
                    if (!$team->hasMember($this->user())) {
                        $fail('You must be a member of the team to move tasks to it.');
                    }
                },
            ],
            'source' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meeting_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meeting_date' => ['sometimes', 'nullable', 'date'],
            'task_name' => ['sometimes', 'required', 'string', 'max:255'],
            'status_id' => [
                'sometimes',
                'required',
                'uuid',
                Rule::exists('task_statuses', 'id'),
            ],
            'priority_id' => [
                'sometimes',
                'nullable',
                'uuid',
                Rule::exists('task_priorities', 'id'),
            ],
            'label_id' => [
                'sometimes',
                'nullable',
                'uuid',
                Rule::exists('task_labels', 'id'),
            ],
            'automation_rule_id' => [
                'sometimes',
                'nullable',
                'uuid',
                Rule::exists('automation_rules', 'id'),
            ],
            'due_date' => ['sometimes', 'nullable', 'date'],
            'cco' => ['sometimes', 'nullable', 'string', 'max:255'],
            'add_assignees' => ['sometimes', 'array'],
            'add_assignees.*' => [
                'uuid',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('status', 'active');
                }),
                function ($attribute, $value, $fail) use ($task) {
                    if ($task->assignees->contains($value)) {
                        $fail('User is already assigned to this task.');
                    }
                },
            ],
            'remove_assignees' => ['sometimes', 'array'],
            'remove_assignees.*' => [
                'uuid',
                Rule::exists('users', 'id'),
                function ($attribute, $value, $fail) use ($task) {
                    if (!$task->assignees->contains($value)) {
                        $fail('User is not assigned to this task.');
                    }
                },
            ],
            'add_attachments' => ['sometimes', 'array'],
            'add_attachments.*' => [
                'file',
                'max:10240', // 10MB
                'mimes:jpeg,png,pdf,doc,docx,xls,xlsx,ppt,pptx,txt',
            ],
            'remove_attachments' => ['sometimes', 'array'],
            'remove_attachments.*' => [
                'uuid',
                Rule::exists('task_attachments', 'id')->where('task_id', $task->id),
            ],
            'custom_fields' => ['sometimes', 'array'],
            'custom_fields.*' => ['nullable', 'string'],
            'add_subtasks' => ['sometimes', 'array'],
            'add_subtasks.*' => ['required', 'string', 'max:255'],
            'update_subtasks' => ['sometimes', 'array'],
            'update_subtasks.*.id' => [
                'required',
                'uuid',
                Rule::exists('subtasks', 'id')->where('parent_task_id', $task->id),
            ],
            'update_subtasks.*.title' => ['required', 'string', 'max:255'],
            'update_subtasks.*.status' => ['required', 'in:pending,in_progress,completed'],
            'remove_subtasks' => ['sometimes', 'array'],
            'remove_subtasks.*' => [
                'uuid',
                Rule::exists('subtasks', 'id')->where('parent_task_id', $task->id),
            ],
            'add_dependencies' => ['sometimes', 'array'],
            'add_dependencies.*' => [
                'uuid',
                Rule::exists('tasks', 'id')->whereNull('deleted_at'),
                function ($attribute, $value, $fail) use ($task) {
                    if ($value === $task->id) {
                        $fail('A task cannot depend on itself.');
                    }
                    if ($task->dependencies->contains($value)) {
                        $fail('Task is already a dependency.');
                    }
                },
            ],
            'remove_dependencies' => ['sometimes', 'array'],
            'remove_dependencies.*' => [
                'uuid',
                Rule::exists('tasks', 'id'),
                function ($attribute, $value, $fail) use ($task) {
                    if (!$task->dependencies->contains($value)) {
                        $fail('Task is not a dependency.');
                    }
                },
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'team_id.exists' => 'The selected team is invalid.',
            'status_id.exists' => 'The selected status is invalid.',
            'priority_id.exists' => 'The selected priority is invalid.',
            'label_id.exists' => 'The selected label is invalid.',
            'automation_rule_id.exists' => 'The selected automation rule is invalid.',
            'add_assignees.*.exists' => 'One or more selected assignees are invalid or inactive.',
            'remove_assignees.*.exists' => 'One or more assignees to remove are invalid.',
            'add_attachments.*.max' => 'Each attachment must not be larger than 10MB.',
            'add_attachments.*.mimes' => 'Invalid file type. Allowed types: jpeg, png, pdf, doc, docx, xls, xlsx, ppt, pptx, txt.',
            'remove_attachments.*.exists' => 'One or more attachments to remove are invalid.',
            'update_subtasks.*.id.exists' => 'One or more subtasks to update are invalid.',
            'remove_subtasks.*.exists' => 'One or more subtasks to remove are invalid.',
            'add_dependencies.*.exists' => 'One or more dependencies to add are invalid.',
            'remove_dependencies.*.exists' => 'One or more dependencies to remove are invalid.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure arrays are unique
        $arrays = [
            'add_assignees',
            'remove_assignees',
            'remove_attachments',
            'remove_subtasks',
            'add_dependencies',
            'remove_dependencies',
        ];

        foreach ($arrays as $array) {
            if ($this->has($array)) {
                $this->merge([
                    $array => array_unique($this->input($array)),
                ]);
            }
        }
    }
}
