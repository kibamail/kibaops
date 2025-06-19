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
            $fail('Cloud provider type is not implemented yet. Please select a different cloud provider.');

            return;
        }

        if (! is_array($value)) {
            $fail('The credentials must be provided as an array.');

            return;
        }

        $credentialFields = $this->type->credentialFields();

        $requiredFields = collect($credentialFields)->where('required', true);

        if (count($value) !== count($credentialFields)) {
            $fail('The credentials you provided were incomplete.');

            return;
        }

        foreach ($requiredFields as $index => $field) {
            if (empty($value[$index])) {
                $fail("The {$field['label']} field is required.");

                return;
            }
        }

        if (! app(CloudProviderFactory::class)->create($this->type)->verify($value)->success) {
            $fail('The provided credentials could not be verified. Please check your credentials and try again.');
        }
    }
}
