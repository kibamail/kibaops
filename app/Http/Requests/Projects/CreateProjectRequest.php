<?php

namespace App\Http\Requests\Projects;

use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CreateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $workspace = Workspace::findOrFail($this->workspace_id);
        return Gate::allows('update', $workspace);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('projects')],
            'workspace_id' => ['required', 'exists:workspaces,id'],
        ];
    }
}