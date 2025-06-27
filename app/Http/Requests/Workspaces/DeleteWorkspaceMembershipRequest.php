<?php

namespace App\Http\Requests\Workspaces;

use Illuminate\Foundation\Http\FormRequest;

class DeleteWorkspaceMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('workspace'));
    }

    public function rules(): array
    {
        return [];
    }

    public function prepareForValidation(): void
    {
        $workspace = $this->route('workspace');
        $membership = $this->route('membership');

        if ($membership && $membership->workspace_id !== $workspace->id) {
            abort(404);
        }
    }
}
