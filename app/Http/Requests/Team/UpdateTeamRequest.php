<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $team = $this->route('team');
        $user = $this->user();

        return $user->hasPermission('manage_teams') || 
            ($team && $team->leader_id === $user->id);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $team = $this->route('team');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('teams', 'name')->ignore($team->id),
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
            'remove_members' => ['nullable', 'array'],
            'remove_members.*' => [
                'uuid',
                Rule::exists('users', 'id'),
                function ($attribute, $value, $fail) use ($team) {
                    if ($value === $team->leader_id) {
                        $fail('Cannot remove the team leader from the team.');
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
            'name.unique' => 'A team with this name already exists.',
            'leader_id.exists' => 'The selected team leader is invalid or inactive.',
            'members.*.exists' => 'One or more selected team members are invalid or inactive.',
            'remove_members.*.exists' => 'One or more members to remove are invalid.',
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
            'remove_members' => 'members to remove',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure arrays are unique
        if ($this->has('members')) {
            $this->merge([
                'members' => array_unique($this->members),
            ]);
        }

        if ($this->has('remove_members')) {
            $this->merge([
                'remove_members' => array_unique($this->remove_members),
            ]);
        }

        // Ensure we're not trying to add and remove the same members
        if ($this->has('members') && $this->has('remove_members')) {
            $this->merge([
                'members' => array_diff($this->members, $this->remove_members),
            ]);
        }
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator);
    }
}
