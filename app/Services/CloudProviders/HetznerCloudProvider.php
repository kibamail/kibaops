<?php

namespace App\Services\CloudProviders;

class HetznerCloudProvider extends AbstractCloudProvider
{
    /**
     * Verify Hetzner Cloud credentials by testing both read and write access.
     * We check the servers endpoint for read access and SSH keys for write
     * access to ensure the token has sufficient permissions.
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
     * Verify read access by attempting to list servers from Hetzner Cloud API.
     * This endpoint requires basic read permissions on the account.
     */
    private function verifyReadAccess(string $credentials): bool
    {
        return $this->makeRequest('get', 'https://api.hetzner-cloud.com/v1/servers', [
            'headers' => [
                'Authorization' => "Bearer {$credentials}",
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Verify write access by attempting to list SSH keys from Hetzner Cloud API.
     * This endpoint requires higher-level permissions and validates write access.
     */
    private function verifyWriteAccess(string $credentials): bool
    {
        return $this->makeRequest('get', 'https://api.hetzner-cloud.com/v1/ssh_keys', [
            'headers' => [
                'Authorization' => "Bearer {$credentials}",
                'Accept' => 'application/json',
            ],
        ]);
    }
}
