<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission('manage_settings');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'app_name' => ['required', 'string', 'max:255'],
            'app_description' => ['nullable', 'string', 'max:1000'],
            'company_name' => ['required', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:1000'],
            'contact_email' => ['required', 'email'],
            'support_email' => ['required', 'email'],
            'default_timezone' => ['required', 'string', 'timezone'],
            'default_language' => ['required', 'string', 'in:en,es,fr,de'],
            'maintenance_mode' => ['required', 'boolean'],
            'maintenance_message' => [
                'required_if:maintenance_mode,true',
                'nullable',
                'string',
                'max:1000',
            ],
            'registration_enabled' => ['required', 'boolean'],
            'email_verification_required' => ['required', 'boolean'],
            'two_factor_enabled' => ['required', 'boolean'],
            'max_login_attempts' => ['required', 'integer', 'min:3', 'max:10'],
            'login_lockout_minutes' => ['required', 'integer', 'min:1', 'max:60'],
            'password_expiry_days' => ['required', 'integer', 'min:30', 'max:365'],
            'session_lifetime_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'file_upload_max_size' => ['required', 'integer', 'min:1', 'max:100'],
            'allowed_file_types' => ['required', 'array', 'min:1'],
            'allowed_file_types.*' => ['string', 'regex:/^[a-zA-Z0-9]+$/'],
            'smtp_host' => ['required', 'string'],
            'smtp_port' => ['required', 'integer', 'between:1,65535'],
            'smtp_username' => ['required', 'string'],
            'smtp_password' => ['required', 'string'],
            'smtp_encryption' => ['required', 'in:tls,ssl'],
            'smtp_from_address' => ['required', 'email'],
            'smtp_from_name' => ['required', 'string'],
            'backup_enabled' => ['required', 'boolean'],
            'backup_frequency' => [
                'required_if:backup_enabled,true',
                'nullable',
                'string',
                'in:daily,weekly,monthly',
            ],
            'backup_retention_days' => [
                'required_if:backup_enabled,true',
                'nullable',
                'integer',
                'min:1',
                'max:365',
            ],
            'google_analytics_id' => ['nullable', 'string', 'regex:/^UA-\d{4,10}-\d{1,4}$/'],
            'recaptcha_enabled' => ['required', 'boolean'],
            'recaptcha_site_key' => [
                'required_if:recaptcha_enabled,true',
                'nullable',
                'string',
            ],
            'recaptcha_secret_key' => [
                'required_if:recaptcha_enabled,true',
                'nullable',
                'string',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'app_name.required' => 'The application name is required.',
            'company_name.required' => 'The company name is required.',
            'contact_email.required' => 'The contact email is required.',
            'contact_email.email' => 'Please enter a valid contact email address.',
            'support_email.required' => 'The support email is required.',
            'support_email.email' => 'Please enter a valid support email address.',
            'default_timezone.required' => 'The default timezone is required.',
            'default_timezone.timezone' => 'Please select a valid timezone.',
            'default_language.required' => 'The default language is required.',
            'default_language.in' => 'Please select a valid language.',
            'maintenance_message.required_if' => 'The maintenance message is required when maintenance mode is enabled.',
            'smtp_host.required' => 'The SMTP host is required.',
            'smtp_port.required' => 'The SMTP port is required.',
            'smtp_port.between' => 'The SMTP port must be between 1 and 65535.',
            'smtp_username.required' => 'The SMTP username is required.',
            'smtp_password.required' => 'The SMTP password is required.',
            'smtp_encryption.required' => 'The SMTP encryption is required.',
            'smtp_encryption.in' => 'Please select a valid SMTP encryption type.',
            'smtp_from_address.required' => 'The SMTP from address is required.',
            'smtp_from_address.email' => 'Please enter a valid SMTP from address.',
            'smtp_from_name.required' => 'The SMTP from name is required.',
            'backup_frequency.required_if' => 'The backup frequency is required when backups are enabled.',
            'backup_retention_days.required_if' => 'The backup retention period is required when backups are enabled.',
            'google_analytics_id.regex' => 'Please enter a valid Google Analytics ID.',
            'recaptcha_site_key.required_if' => 'The reCAPTCHA site key is required when reCAPTCHA is enabled.',
            'recaptcha_secret_key.required_if' => 'The reCAPTCHA secret key is required when reCAPTCHA is enabled.',
        ];
    }
}
