<?php

namespace App\Services;

use LKDev\HetznerCloud\HetznerAPIClient;
use LKDev\HetznerCloud\Models\SSHKeys\SSHKey;

class HetznerCloudService
{
    private HetznerAPIClient $client;

    public function __construct(string $token)
    {
        $this->client = new HetznerAPIClient($token);
    }

    public function createSshKey(string $name, string $publicKey, array $labels = []): ?SSHKey
    {
        return $this->client->sshKeys()->create($name, $publicKey, $labels);
    }

    public function listSshKeys(): array
    {
        return $this->client->sshKeys()->all();
    }

    public function deleteSshKey(string $keyId): bool
    {
        return $this->client->sshKeys()->delete($keyId);
    }

    public function createLabel(string $key, string $value): bool
    {
        // Hetzner doesn't have standalone labels, they're attached to resources
        return true;
    }

    public function updateLabel(string $key, string $value): bool
    {
        return true;
    }

    public function deleteLabel(string $key): bool
    {
        return true;
    }
}
