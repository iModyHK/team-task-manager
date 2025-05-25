<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission('create_tasks');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'team_id' => [
                'required',
                'uuid',
                Rule::exists('teams', 'id'),
                function ($attribute, $value, $fail) {
                    $team = \App\Models\Team::find($value);
                    if (!$team->hasMember($this->user())) {
                        $fail('You must be a member of the team to create tasks.');
                    }
                },
            ],
            'source' => ['nullable', 'string', 'max:255'],
            'meeting_title' => ['nullable', 'string', 'max:255'],
            'meeting_date' => ['nullable', 'date'],
            'task_name' => ['required', 'string', 'max:255'],
            'status_id' => [
                'required',
                'uuid',
                Rule::exists('task_statuses', 'id'),
            ],
            'priority_id' => [
                'nullable',
                'uuid',
                Rule::exists('task_priorities', 'id'),
            ],
            'label_id' => [
                'nullable',
                'uuid',
                Rule::exists('task_labels', 'id'),
            ],
            'automation_rule_id' => [
                'nullable',
                'uuid',
                Rule::exists('automation_rules', 'id'),
            ],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'cco' => ['nullable', 'string', 'max:255'],
            'assignees' => ['required', 'array', 'min:1'],
            'assignees.*' => [
                'uuid',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('status', 'active');
                }),
            ],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => [
                'file',
                'max:10240', // 10MB
                'mimes:jpeg,png,pdf,doc,docx,xls,xlsx,ppt,pptx,txt',
            ],
            'custom_fields' => ['nullable', 'array'],
            'custom_fields.*' => ['nullable', 'string'],
            'subtasks' => ['nullable', 'array'],
            'subtasks.*' => ['required', 'string', 'max:255'],
            'dependencies' => ['nullable', 'array'],
            'dependencies.*' => [
                'uuid',
                Rule::exists('tasks', 'id')->whereNull('deleted_at'),
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
            'assignees.required' => 'At least one assignee is required.',
            'assignees.*.exists' => 'One or more selected assignees are invalid or inactive.',
            'attachments.*.max' => 'Each attachment must not be larger than 10MB.',
            'attachments.*.mimes' => 'Invalid file type. Allowed types: jpeg, png, pdf, doc, docx, xls, xlsx, ppt, pptx, txt.',
            'dependencies.*.exists' => 'One or more selected dependencies are invalid.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'team_id' => 'team',
            'source' => 'source',
            'meeting_title' => 'meeting title',
            'meeting_date' => 'meeting date',
            'task_name' => 'task name',
            'status_id' => 'status',
            'priority_id' => 'priority',
            'label_id' => 'label',
            'automation_rule_id' => 'automation rule',
            'due_date' => 'due date',
            'cco' => 'CCO',
            'assignees' => 'assignees',
            'attachments' => 'attachments',
            'custom_fields' => 'custom fields',
            'subtasks' => 'subtasks',
            'dependencies' => 'dependencies',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure arrays are unique
        if ($this->has('assignees')) {
            $this->merge([
                'assignees' => array_unique($this->assignees),
            ]);
        }

        if ($this->has('dependencies')) {
            $this->merge([
                'dependencies' => array_unique($this->dependencies),
            ]);
        }
    }
}
