<?php

namespace App\Services\CloudProviders;

use Illuminate\Support\Facades\Log;

class HetznerCloudProvider extends AbstractCloudProvider
{
    /**
     * Verify Hetzner Cloud credentials by testing both read and write access.
     * We check the servers endpoint for read access and SSH keys for write
     * access to ensure the token has sufficient permissions.
     *
     * @param array $credentials Array containing [token]
     */
    public function verify(array $credentials): CloudProviderResponse
    {
        if (empty($credentials) || !isset($credentials[0])) {
            return CloudProviderResponse::failure(
                message: 'No credentials provided',
                errors: ['credentials' => 'Token is required for Hetzner Cloud']
            );
        }

        $token = $credentials[0];

        if (empty(trim($token))) {
            return CloudProviderResponse::failure(
                message: 'Invalid credentials provided',
                errors: ['credentials' => 'Token cannot be empty']
            );
        }

        Log::info('Verifying Hetzner Cloud credentials', [
            'token_prefix' => substr($token, 0, 8) . '***'
        ]);

        $readResponse = $this->verifyReadAccess($token);
        if (!$readResponse->success) {
            Log::error('Hetzner Cloud read access verification failed', $readResponse->toArray());
            return $readResponse;
        }

        $writeResponse = $this->verifyWriteAccess($token);
        if (!$writeResponse->success) {
            Log::error('Hetzner Cloud write access verification failed', $writeResponse->toArray());
            return $writeResponse;
        }

        Log::info('Hetzner Cloud credentials verified successfully');

        return CloudProviderResponse::success(
            message: 'Hetzner Cloud credentials verified successfully',
            rawResponse: [
                'read_access' => $readResponse->rawResponse,
                'write_access' => $writeResponse->rawResponse,
            ],
            attemptCount: max($readResponse->attemptCount, $writeResponse->attemptCount)
        );
    }

    /**
     * Verify read access by attempting to list servers from Hetzner Cloud API.
     * This endpoint requires basic read permissions on the account.
     */
    private function verifyReadAccess(string $token): CloudProviderResponse
    {
        return $this->makeRequest('get', 'https://api.hetzner.cloud/v1/servers', [
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Verify write access by attempting to list SSH keys from Hetzner Cloud API.
     * This endpoint requires higher-level permissions and validates write access.
     */
    private function verifyWriteAccess(string $token): CloudProviderResponse
    {
        return $this->makeRequest('get', 'https://api.hetzner.cloud/v1/ssh_keys', [
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Extract error message from Hetzner Cloud API response.
     * Hetzner uses a specific error format with 'error' object containing 'message'.
     */
    protected function extractProviderErrorMessage(array $responseBody): ?string
    {
        return $responseBody['error']['message'] ??
               $responseBody['message'] ??
               null;
    }
}
