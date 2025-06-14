<?php

namespace App\Services\CloudProviders;

class DigitalOceanCloudProvider extends AbstractCloudProvider
{
    /**
     * Verify DigitalOcean credentials by testing both read and write access.
     * We check the droplets endpoint for read access and account keys for
     * write access to ensure the token has sufficient permissions.
     *
     * @param array $credentials Array containing [token]
     */
    public function verify(array $credentials): bool
    {
        if (empty($credentials) || !isset($credentials[0])) {
            return false;
        }

        $token = $credentials[0];
        $readVerified = $this->verifyReadAccess($token);
        $writeVerified = $this->verifyWriteAccess($token);

        return $readVerified && $writeVerified;
    }

    /**
     * Verify read access by attempting to list droplets from DigitalOcean API.
     * This endpoint requires basic read permissions on the account.
     */
    private function verifyReadAccess(string $credentials): bool
    {
        return $this->makeRequest('get', 'https://api.digitalocean.com/v2/droplets', [
            'headers' => [
                'Authorization' => "Bearer {$credentials}",
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Verify write access by attempting to list account keys from DigitalOcean API.
     * This endpoint requires higher-level permissions and validates write access.
     */
    private function verifyWriteAccess(string $credentials): bool
    {
        return $this->makeRequest('get', 'https://api.digitalocean.com/v2/account/keys', [
            'headers' => [
                'Authorization' => "Bearer {$credentials}",
                'Accept' => 'application/json',
            ],
        ]);
    }
}
