<?php

namespace App\Rules;

use App\Enums\CloudProviderType;
use App\Services\CloudProviders\CloudProviderFactory;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCloudProviderCredentials implements ValidationRule
{
    /**
     * Create a new validation rule instance for the specified cloud provider type.
     */
    public function __construct(
        private CloudProviderType $type
    ) {}

    /**
     * Validate that the provided credentials are valid for the cloud provider.
     * This rule creates the appropriate provider instance and calls its verify
     * method to test the credentials against the provider's API endpoints.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! in_array($this->type, CloudProviderType::implemented())) {
            return;
        }

        // Ensure value is an array
        if (!is_array($value)) {
            $fail('The credentials must be provided as an array.');
            return;
        }

        // Validate that all required credential fields are provided
        $credentialFields = $this->type->credentialFields();
        $requiredFields = collect($credentialFields)->where('required', true);

        if (count($value) !== count($credentialFields)) {
            $fail('The number of credential values must match the required fields.');
            return;
        }

        // Validate each credential field is not empty if required
        foreach ($requiredFields as $index => $field) {
            if (empty($value[$index])) {
                $fail("The {$field['label']} field is required.");
                return;
            }
        }

        $factory = app(CloudProviderFactory::class);
        $provider = $factory->create($this->type);

        if (! $provider->verify($value)) {
            $fail('The provided credentials could not be verified. Please check your credentials and try again.');
        }
    }
}
