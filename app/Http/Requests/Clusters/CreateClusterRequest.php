<?php

namespace App\Http\Requests\Clusters;

use Illuminate\Foundation\Http\FormRequest;

class CreateClusterRequest extends FormRequest
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

        return $workspace && $this->user()->can('update', $workspace);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:64', 'regex:/^[a-zA-Z0-9\s\-_]+$/'],
            'cloud_provider_id' => [
                'required',
                'string',
                'exists:cloud_providers,id',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $activeWorkspaceId = session('active_workspace_id');
                    if (! $activeWorkspaceId) {
                        $fail('No active workspace found.');

                        return;
                    }

                    $workspace = \App\Models\Workspace::find($activeWorkspaceId);
                    if (! $workspace || ! $workspace->cloudProviders()->where('id', $value)->exists()) {
                        $fail('The selected cloud provider does not belong to this workspace.');
                    }
                },
            ],
            'region' => [
                'required',
                'string',
                'max:64',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $cloudProviderId = $this->input('cloud_provider_id');
                    if (! $cloudProviderId) {
                        return;
                    }

                    $cloudProvider = \App\Models\CloudProvider::find($cloudProviderId);
                    if (! $cloudProvider) {
                        return;
                    }

                    $validRegions = $cloudProvider->type->getValidRegionSlugs();
                    if (! in_array($value, $validRegions)) {
                        $fail("The selected region is not supported by {$cloudProvider->type->label()}.");
                    }
                },
            ],
            'worker_nodes_count' => ['required', 'integer', 'min:3', 'max:50'],
            'storage_nodes_count' => [
                'required_unless:shared_storage_worker_nodes,true',
                'integer',
                'min:0',
                'max:50',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (! $this->boolean('shared_storage_worker_nodes') && $value < 3) {
                        $fail('Storage nodes count must be at least 3 when not using shared storage/worker nodes.');
                    }
                },
            ],
            'shared_storage_worker_nodes' => ['sometimes', 'boolean'],
            'server_type' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $cloudProviderId = $this->input('cloud_provider_id');
                    if (! $cloudProviderId) {
                        return;
                    }

                    $cloudProvider = \App\Models\CloudProvider::find($cloudProviderId);
                    if (! $cloudProvider) {
                        return;
                    }

                    $validServerTypes = $cloudProvider->type->getValidServerTypes();
                    if (! in_array($value, $validServerTypes)) {
                        $fail("The selected server type is not supported by {$cloudProvider->type->label()}.");
                    }
                },
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    public function prepareForValidation(): void
    {
        $this->merge([
            'shared_storage_worker_nodes' => $this->boolean('shared_storage_worker_nodes', false),
        ]);
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Cluster name is required.',
            'name.regex' => 'Cluster name can only contain letters, numbers, spaces, hyphens, and underscores.',
            'cloud_provider_id.required' => 'A cloud provider must be selected.',
            'cloud_provider_id.exists' => 'The selected cloud provider is invalid.',
            'region.required' => 'A region must be specified.',
            'worker_nodes_count.required' => 'Worker nodes count is required.',
            'worker_nodes_count.min' => 'At least 3 worker nodes are required.',
            'worker_nodes_count.max' => 'Maximum 50 worker nodes allowed.',
            'storage_nodes_count.min' => 'At least 3 storage nodes are required when not using shared storage/worker nodes.',
            'storage_nodes_count.max' => 'Maximum 50 storage nodes allowed.',
            'server_type.required' => 'A server type must be selected.',
            'server_type.string' => 'Server type must be a valid string.',
        ];
    }
}
