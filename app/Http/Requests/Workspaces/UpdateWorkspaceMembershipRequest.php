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
        $workspace = $this->route('workspace');

        return [
            'project_ids' => ['required', 'array', 'min:1'],
            'project_ids.*' => [
                'required',
                'integer',
                'exists:projects,id',
                function (string $attribute, mixed $value, \Closure $fail) use ($workspace) {
                    if (!$workspace->projects()->where('id', $value)->exists()) {
                        $fail('The selected project does not belong to this workspace.');
                    }
                }
            ],
            'role' => ['sometimes', Rule::enum(WorkspaceMembershipRole::class)],
        ];
    }

    public function prepareForValidation(): void
    {
        $workspace = $this->route('workspace');
        $membership = $this->route('membership');

        if ($membership && $membership->workspace_id !== $workspace->id) {
            abort(404);
        }
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
