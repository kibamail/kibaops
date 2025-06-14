<?php

namespace App\Http\Requests\Workspaces;

use App\Enums\WorkspaceMembershipRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateWorkspaceMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('workspace'));
    }

    public function rules(): array
    {
        $workspace = $this->route('workspace');

        return [
            'emails' => ['required', 'array', 'min:1'],
            'emails.*' => ['required', 'email', 'max:255'],
            'project_ids' => ['required', 'array', 'min:1'],
            'project_ids.*' => [
                'required',
                'integer',
                'exists:projects,id',
                function (string $attribute, mixed $value, \Closure $fail) use ($workspace) {
                    if (! $workspace->projects()->where('id', $value)->exists()) {
                        $fail('The selected project does not belong to this workspace.');
                    }
                },
            ],
            'role' => ['required', Rule::enum(WorkspaceMembershipRole::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'emails.required' => 'At least one email address is required.',
            'emails.*.email' => 'Each email must be a valid email address.',
            'project_ids.required' => 'At least one project must be selected.',
            'project_ids.*.exists' => 'One or more selected projects do not exist.',
            'role.required' => 'A role must be selected.',
            'role.enum' => 'The selected role is invalid.',
        ];
    }
}
