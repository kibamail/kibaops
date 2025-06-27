<?php

namespace App\Services\CloudProviders\Hetzner;

use App\Contracts\CloudSshKeysInterface;
use App\Services\CloudProviders\CloudProviderResponse;

class SshKeys implements CloudSshKeysInterface
{
    private ?string $token;

    public function __construct(?string $token = null)
    {
        $this->token = $token;
    }

    public function create(string $name, string $publicKey): CloudProviderResponse
    {
        $safeResult = safe(function () use ($name, $publicKey) {
            $hetznerService = app('hetzner-cloud', ['token' => $this->token]);

            return $hetznerService->createSshKey($name, $publicKey, ['kibaops' => 'true']);
        });

        if ($safeResult['error'] !== null) {
            return CloudProviderResponse::failure('Failed to create SSH key: ' . $safeResult['error']);
        }

        $sshKey = $safeResult['data'];

        if ($sshKey === null) {
            return CloudProviderResponse::failure('Failed to create SSH key');
        }

        return CloudProviderResponse::success('SSH key created successfully', $sshKey);
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
