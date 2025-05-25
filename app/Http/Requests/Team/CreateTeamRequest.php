<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTeamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission('manage_teams');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('teams', 'name'),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'leader_id' => [
                'required',
                'uuid',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('status', 'active');
                }),
            ],
            'members' => ['nullable', 'array'],
            'members.*' => [
                'uuid',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('status', 'active');
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
            'name.unique' => 'A team with this name already exists.',
            'leader_id.exists' => 'The selected team leader is invalid or inactive.',
            'members.*.exists' => 'One or more selected team members are invalid or inactive.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'team name',
            'description' => 'team description',
            'leader_id' => 'team leader',
            'members' => 'team members',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure members array is unique
        if ($this->has('members')) {
            $this->merge([
                'members' => array_unique($this->members),
            ]);
        }
    }
}
