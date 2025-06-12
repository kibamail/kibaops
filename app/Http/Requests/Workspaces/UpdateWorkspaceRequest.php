<?php

namespace App\Http\Requests\Workspaces;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkspaceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('workspace'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:32', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'slug' => ['required', 'string', 'unique:workspaces,slug,'.$this->route('workspace')->id],
        ];
    }
}
