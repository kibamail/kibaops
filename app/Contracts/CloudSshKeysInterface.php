<?php

namespace App\Contracts;

use App\Services\CloudProviders\CloudProviderResponse;

interface CloudSshKeysInterface
{
    /**
     * Create a new SSH key in the cloud provider.
     *
     * @param  string  $name  The SSH key name
     * @param  string  $publicKey  The SSH public key content
     * @return CloudProviderResponse Response indicating success or failure
     */
    public function create(string $name, string $publicKey): CloudProviderResponse;

    /**
     * Update an existing SSH key in the cloud provider.
     *
     * @param  string  $keyId  The SSH key ID to update
     * @param  string  $name  The new SSH key name
     * @return CloudProviderResponse Response indicating success or failure
     */
    public function update(string $keyId, string $name): CloudProviderResponse;

    /**
     * Delete an SSH key from the cloud provider.
     *
     * @param  string  $keyId  The SSH key ID to delete
     * @return CloudProviderResponse Response indicating success or failure
     */
    public function delete(string $keyId): CloudProviderResponse;
}
