<?php

namespace App\Services\CloudProviders\DigitalOcean;

use App\Contracts\CloudSshKeysInterface;
use App\Services\CloudProviders\CloudProviderResponse;

class SshKeys implements CloudSshKeysInterface
{
    public function create(string $name, string $publicKey): CloudProviderResponse
    {
        return CloudProviderResponse::success('SSH key created successfully');
    }

    public function update(string $keyId, string $name): CloudProviderResponse
    {
        return CloudProviderResponse::success('SSH key updated successfully');
    }

    public function delete(string $keyId): CloudProviderResponse
    {
        return CloudProviderResponse::success('SSH key deleted successfully');
    }
}
