<?php

namespace App\Http\Requests\CloudProviders;

use App\Rules\ValidCloudProviderCredentials;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCloudProviderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to update this cloud provider.
     * Only users who can update the workspace are allowed to modify
     * its cloud providers.
     */
    public function authorize(): bool
    {
        $workspace = $this->route('workspace');

        return $this->user()->can('update', $workspace);
    }

    /**
     * Get the validation rules that apply to updating a cloud provider.
     * Both name and credentials are optional, but at least one must be
     * provided. If credentials are provided, they are verified.
     */
    public function rules(): array
    {
        $cloudProvider = $this->route('cloud_provider');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:32',
            ],
            'credentials' => [
                'sometimes',
                'string',
                $this->filled('credentials') ? new ValidCloudProviderCredentials($cloudProvider->type) : null,
            ],
        ];
    }

    /**
     * Configure the validator to ensure at least one field is provided.
     * This custom validation runs after the standard rules and ensures
     * that the update request contains meaningful changes.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->has('name') && ! $this->has('credentials')) {
                $validator->errors()->add('name', 'At least one of the following fields is required: name or credentials.');
            }
        });
    }

    /**
     * Get custom error messages for the validation rules.
     */
    public function messages(): array
    {
        return [
            'name.max' => 'The provider name must not exceed 32 characters.',
            'credentials.string' => 'The credentials must be a string.',
        ];
    }
}
