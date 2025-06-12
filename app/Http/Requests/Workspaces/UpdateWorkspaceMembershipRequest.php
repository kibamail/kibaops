<?php

namespace App\Http\Requests\Workspaces;

use App\Enums\WorkspaceMembershipRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkspaceMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('workspace'));
    }

    public function rules(): array
    {
        return [
            'project_ids' => ['required', 'array', 'min:1'],
            'project_ids.*' => ['required', 'integer', 'exists:projects,id'],
            'role' => ['sometimes', Rule::enum(WorkspaceMembershipRole::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'project_ids.required' => 'At least one project must be selected.',
            'project_ids.*.exists' => 'One or more selected projects do not exist.',
            'role.enum' => 'The selected role is invalid.',
        ];
    }
}
