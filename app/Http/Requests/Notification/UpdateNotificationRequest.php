<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $notification = $this->route('notification');
        return $notification && $notification->user_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'read' => ['required', 'boolean'],
            'notification_ids' => [
                'required_without:read',
                'array',
            ],
            'notification_ids.*' => [
                'uuid',
                Rule::exists('notifications', 'id')->where(function ($query) {
                    $query->where('user_id', $this->user()->id);
                }),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'notification_ids.required_without' => 'Please select at least one notification.',
            'notification_ids.*.exists' => 'One or more selected notifications are invalid.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'read' => 'read status',
            'notification_ids' => 'notifications',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('notification_ids')) {
            $this->merge([
                'notification_ids' => array_unique($this->notification_ids),
            ]);
        }
    }
}
