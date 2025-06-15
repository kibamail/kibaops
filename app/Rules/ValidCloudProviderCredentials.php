<?php

namespace App\Rules;

use App\Enums\CloudProviderType;
use App\Services\CloudProviders\CloudProviderFactory;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;

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
        Log::info('ValidCloudProviderCredentials: Starting validation', [
            'attribute' => $attribute,
            'provider_type' => $this->type->value,
            'is_implemented' => in_array($this->type, CloudProviderType::implemented()),
        ]);

        if (! in_array($this->type, CloudProviderType::implemented())) {
            Log::info('ValidCloudProviderCredentials: Provider type not implemented, skipping validation');
            return;
        }

        if (!is_array($value)) {
            Log::warning('ValidCloudProviderCredentials: Credentials not provided as array', [
                'value_type' => gettype($value),
            ]);
            $fail('The credentials must be provided as an array.');
            return;
        }

        $credentialFields = $this->type->credentialFields();
        $requiredFields = collect($credentialFields)->where('required', true);

        Log::info('ValidCloudProviderCredentials: Checking credential structure', [
            'provided_count' => count($value),
            'expected_count' => count($credentialFields),
            'required_fields_count' => $requiredFields->count(),
        ]);

        if (count($value) !== count($credentialFields)) {
            Log::warning('ValidCloudProviderCredentials: Credential count mismatch', [
                'provided' => count($value),
                'expected' => count($credentialFields),
            ]);
            $fail('The number of credential values must match the required fields.');
            return;
        }

        foreach ($requiredFields as $index => $field) {
            if (empty($value[$index])) {
                Log::warning('ValidCloudProviderCredentials: Required field missing', [
                    'field_index' => $index,
                    'field_label' => $field['label'],
                ]);
                $fail("The {$field['label']} field is required.");
                return;
            }
        }

        Log::info('ValidCloudProviderCredentials: Creating provider instance for verification');
        $factory = app(CloudProviderFactory::class);
        $provider = $factory->create($this->type);

        Log::info('ValidCloudProviderCredentials: Starting provider verification', [
            'provider_class' => get_class($provider),
        ]);

        $verificationResponse = $provider->verify($value);

        Log::info('ValidCloudProviderCredentials: Provider verification completed', [
            'verification_response' => $verificationResponse->toArray(),
        ]);

        if (!$verificationResponse->success) {
            Log::warning('ValidCloudProviderCredentials: Credential verification failed', [
                'error_message' => $verificationResponse->message,
                'provider_message' => $verificationResponse->providerMessage,
                'http_status_code' => $verificationResponse->httpStatusCode,
                'attempt_count' => $verificationResponse->attemptCount,
            ]);

            $fail('The provided credentials could not be verified. Please check your credentials and try again.');
        } else {
            Log::info('ValidCloudProviderCredentials: Credential verification successful', [
                'message' => $verificationResponse->message,
                'attempt_count' => $verificationResponse->attemptCount,
            ]);
        }
    }
}
