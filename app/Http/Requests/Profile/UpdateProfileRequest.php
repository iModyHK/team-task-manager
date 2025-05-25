<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $user = $this->user();

        return [
            'username' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('users')->ignore($user->id),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:100',
                Rule::unique('users')->ignore($user->id),
            ],
            'full_name' => ['required', 'string', 'max:100'],
            'avatar' => ['nullable', 'image', 'max:2048'], // 2MB max
            'current_password' => ['required_with:new_password', 'current_password'],
            'new_password' => ['nullable', 'confirmed', 'min:8'],
            'timezone' => ['required', 'string', 'timezone'],
            'language' => ['required', 'string', 'in:en,es,fr,de'], // Add supported languages
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'username.regex' => 'Username may only contain letters, numbers, dashes, and underscores.',
            'current_password.current_password' => 'The provided password does not match your current password.',
            'avatar.max' => 'The avatar must not be larger than 2MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'username' => 'username',
            'email' => 'email address',
            'full_name' => 'full name',
            'current_password' => 'current password',
            'new_password' => 'new password',
            'timezone' => 'timezone',
            'language' => 'language',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'username' => strtolower($this->username),
            'email' => strtolower($this->email),
        ]);
    }
}
