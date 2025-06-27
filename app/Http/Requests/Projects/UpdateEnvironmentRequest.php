<?php

namespace App\Http\Requests\Projects;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEnvironmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $environment = $this->route('environment');

        return $this->user()->can('update', $environment);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $project = $this->route('project');
        $environment = $this->route('environment');

        return [
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                function (string $attribute, mixed $value, \Closure $fail) use ($project, $environment) {
                    if ($project->environments()->where('slug', $value)->where('id', '!=', $environment->id)->exists()) {
                        $fail('The slug has already been taken for this project.');
                    }
                },
            ],
        ];
    }

    public function prepareForValidation(): void
    {
        $project = $this->route('project');
        $environment = $this->route('environment');

        if ($environment && $environment->project_id !== $project->id) {
            abort(404);
        }
    }
}
