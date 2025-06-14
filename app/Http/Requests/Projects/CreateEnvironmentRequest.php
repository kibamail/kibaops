<?php

namespace App\Http\Requests\Projects;

use App\Models\Environment;
use Illuminate\Foundation\Http\FormRequest;

class CreateEnvironmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $this->user()->can('create', [Environment::class, $project]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                function (string $attribute, mixed $value, \Closure $fail) use ($project) {
                    if ($project->environments()->where('slug', $value)->exists()) {
                        $fail('The slug has already been taken for this project.');
                    }
                },
            ],
        ];
    }
}
