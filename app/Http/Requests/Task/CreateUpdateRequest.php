<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class CreateUpdateRequest extends FormRequest
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
        return [
            'content' => ['required', 'string', 'max:10000'],
            'ai_generated' => ['sometimes', 'boolean'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => [
                'file',
                'max:10240', // 10MB
                'mimes:jpeg,png,pdf,doc,docx,xls,xlsx,ppt,pptx,txt',
            ],
            'mentioned_users' => ['nullable', 'array'],
            'mentioned_users.*' => ['uuid', 'exists:users,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'content.required' => 'The update content is required.',
            'content.max' => 'The update content may not be greater than 10000 characters.',
            'attachments.*.max' => 'Each attachment must not be larger than 10MB.',
            'attachments.*.mimes' => 'Invalid file type. Allowed types: jpeg, png, pdf, doc, docx, xls, xlsx, ppt, pptx, txt.',
            'mentioned_users.*.exists' => 'One or more mentioned users are invalid.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'content' => 'update content',
            'ai_generated' => 'AI generated',
            'attachments' => 'attachments',
            'mentioned_users' => 'mentioned users',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Extract mentioned users from content using regex
        preg_match_all('/@([a-zA-Z0-9_-]+)/', $this->content, $matches);
        
        if (!empty($matches[1])) {
            $mentionedUsernames = $matches[1];
            $mentionedUsers = \App\Models\User::whereIn('username', $mentionedUsernames)
                ->pluck('id')
                ->toArray();
            
            $this->merge([
                'mentioned_users' => $mentionedUsers,
            ]);
        }
    }
}
