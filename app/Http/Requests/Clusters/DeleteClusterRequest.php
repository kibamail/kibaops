<?php

namespace App\Http\Requests\Clusters;

use Illuminate\Foundation\Http\FormRequest;

class DeleteClusterRequest extends FormRequest
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
        return [];
    }
}
