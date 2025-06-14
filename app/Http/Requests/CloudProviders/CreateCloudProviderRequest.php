<?php

namespace App\Http\Requests\CloudProviders;

use App\Enums\CloudProviderType;
use App\Rules\ImplementedCloudProviderType;
use App\Rules\ValidCloudProviderCredentials;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCloudProviderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to create a cloud provider for this workspace.
     * Only users who can update the workspace are allowed to add cloud providers.
     */
    public function authorize(): bool
    {
        $workspace = $this->route('workspace');
        return $this->user()->can('update', $workspace);
    }

    /**
     * Get the validation rules that apply to creating a new cloud provider.
     * The rules ensure the provider type is implemented and credentials
     * are verified before allowing the provider to be created.
     */
    public function rules(): array
    {
        $type = CloudProviderType::tryFrom($this->input('type'));

        return [
            'name' => ['required', 'string', 'max:32'],
            'type' => [
                'required',
                Rule::enum(CloudProviderType::class),
                new ImplementedCloudProviderType(),
            ],
            'credentials' => [
                'required',
                'string',
                $type ? new ValidCloudProviderCredentials($type) : null,
            ],
        ];
    }

    /**
     * Prepare the data for validation by adding the workspace ID from the route.
     */
    public function prepareForValidation(): void
    {
        $this->merge([
            'workspace_id' => $this->route('workspace')->id,
        ]);
    }

    /**
     * Get custom error messages for the validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The provider name is required.',
            'name.max' => 'The provider name must not exceed 32 characters.',
            'type.required' => 'The provider type is required.',
            'type.enum' => 'The selected provider type is invalid.',
            'credentials.required' => 'The credentials are required.',
        ];
    }
}
