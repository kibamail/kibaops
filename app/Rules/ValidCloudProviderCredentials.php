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
        if (!in_array($this->type, CloudProviderType::implemented())) {
            return;
        }

        $factory = app(CloudProviderFactory::class);
        $provider = $factory->create($this->type);

        if (!$provider->verify($value)) {
            $fail('The provided credentials could not be verified. Please check your credentials and try again.');
        }
    }
}
