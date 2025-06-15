<?php

namespace App\Services\CloudProviders;

use Illuminate\Support\Facades\Log;

class DigitalOceanCloudProvider extends AbstractCloudProvider
{
    /**
     * Verify DigitalOcean credentials by testing both read and write access.
     * We check the droplets endpoint for read access and account keys for
     * write access to ensure the token has sufficient permissions.
     *
     * @param array $credentials Array containing [token]
     */
    public function verify(array $credentials): CloudProviderResponse
    {
        if (empty($credentials) || !isset($credentials[0])) {
            return CloudProviderResponse::failure(
                message: 'No credentials provided',
                errors: ['credentials' => 'Token is required for DigitalOcean']
            );
        }

        $token = $credentials[0];

        if (empty(trim($token))) {
            return CloudProviderResponse::failure(
                message: 'Invalid credentials provided',
                errors: ['credentials' => 'Token cannot be empty']
            );
        }

        Log::info('Verifying DigitalOcean credentials', [
            'token_prefix' => substr($token, 0, 8) . '***'
        ]);

        $readResponse = $this->verifyReadAccess($token);
        if (!$readResponse->success) {
            Log::error('DigitalOcean read access verification failed', $readResponse->toArray());
            return $readResponse;
        }

        $writeResponse = $this->verifyWriteAccess($token);
        if (!$writeResponse->success) {
            Log::error('DigitalOcean write access verification failed', $writeResponse->toArray());
            return $writeResponse;
        }

        Log::info('DigitalOcean credentials verified successfully');

        return CloudProviderResponse::success(
            message: 'DigitalOcean credentials verified successfully',
            rawResponse: [
                'read_access' => $readResponse->rawResponse,
                'write_access' => $writeResponse->rawResponse,
            ],
            attemptCount: max($readResponse->attemptCount, $writeResponse->attemptCount)
        );
    }

    /**
     * Verify read access by attempting to list droplets from DigitalOcean API.
     * This endpoint requires basic read permissions on the account.
     */
    private function verifyReadAccess(string $token): CloudProviderResponse
    {
        return $this->makeRequest('get', 'https://api.digitalocean.com/v2/droplets', [
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Verify write access by attempting to list account keys from DigitalOcean API.
     * This endpoint requires higher-level permissions and validates write access.
     */
    private function verifyWriteAccess(string $token): CloudProviderResponse
    {
        return $this->makeRequest('get', 'https://api.digitalocean.com/v2/account/keys', [
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Extract error message from DigitalOcean API response.
     * DigitalOcean uses various error formats including 'message', 'error_message', and nested errors.
     */
    protected function extractProviderErrorMessage(array $responseBody): ?string
    {
        return $responseBody['message'] ??
               $responseBody['error_message'] ??
               $responseBody['errors'][0]['message'] ??
               null;
    }
}
