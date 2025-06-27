<?php

namespace App\Http\Requests\Clusters;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClusterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $activeWorkspaceId = session('active_workspace_id');

        if (! $activeWorkspaceId) {
            return false;
        }

        $workspace = \App\Models\Workspace::find($activeWorkspaceId);
        $cluster = $this->route('cluster');

        return $workspace && $cluster &&
               $cluster->workspace_id === $workspace->id &&
               $this->user()->can('update', $workspace);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'string',
                'max:64',
                'regex:/^[a-zA-Z0-9\s\-_]+$/',
            ],
            'shared_storage_worker_nodes' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    public function prepareForValidation(): void
    {
        // Authorization and workspace validation is handled in the controller
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'Cluster name can only contain letters, numbers, spaces, hyphens, and underscores.',
            'name.max' => 'Cluster name cannot exceed 64 characters.',
        ];
    }
}
