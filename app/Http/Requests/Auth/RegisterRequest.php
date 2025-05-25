<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
        return [
            'username' => [
                'required',
                'string',
                'max:50',
                'unique:users,username',
                'regex:/^[a-zA-Z0-9_-]+$/',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:100',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'full_name' => ['required', 'string', 'max:100'],
            'terms_accepted' => ['required', 'accepted'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'username.regex' => 'Username may only contain letters, numbers, dashes, and underscores.',
            'terms_accepted.required' => 'You must accept the terms of service.',
            'terms_accepted.accepted' => 'You must accept the terms of service.',
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
            'password' => 'password',
            'full_name' => 'full name',
            'terms_accepted' => 'terms of service',
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
