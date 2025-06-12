<?php

namespace App\Http\Requests\Workspaces;

use Illuminate\Foundation\Http\FormRequest;

class CreateWorkspaceMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('workspace'));
    }

    public function rules(): array
    {
        return [
            'emails' => ['required', 'array', 'min:1'],
            'emails.*' => ['required', 'email', 'max:255'],
            'project_ids' => ['required', 'array', 'min:1'],
            'project_ids.*' => ['required', 'integer', 'exists:projects,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'emails.required' => 'At least one email address is required.',
            'emails.*.email' => 'Each email must be a valid email address.',
            'project_ids.required' => 'At least one project must be selected.',
            'project_ids.*.exists' => 'One or more selected projects do not exist.',
        ];
    }
}
