<?php

namespace App\Services\CloudProviders;

class DigitalOceanCloudProvider extends AbstractCloudProvider
{
    /**
     * Verify DigitalOcean credentials by testing both read and write access.
     * We check the droplets endpoint for read access and account keys for
     * write access to ensure the token has sufficient permissions.
     */
    public function verify(string $credentials): bool
    {
        $readVerified = $this->verifyReadAccess($credentials);
        $writeVerified = $this->verifyWriteAccess($credentials);

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
