<?php

namespace App\Services\CloudProviders\Hetzner;

use App\Contracts\CloudLabelsInterface;
use App\Contracts\CloudProviderInterface;
use App\Contracts\CloudSshKeysInterface;
use App\Jobs\DeleteTestSshKeyJob;
use App\Services\CloudProviders\CloudProviderResponse;
use App\Services\SshKeyGenerator;

class HetznerCloudProvider implements CloudProviderInterface
{
    private ?string $token = null;

    public function __construct(?string $token = null)
    {
        $this->token = $token;
    }

    public function verify(array $credentials): CloudProviderResponse
    {
        if (empty($credentials) || ! isset($credentials[0])) {
            return CloudProviderResponse::failure('No credentials provided');
        }

        $token = $credentials[0];

        if (empty(trim($token))) {
            return CloudProviderResponse::failure('Invalid credentials provided');
        }

        $this->token = $token;

        $name = 'kibaops-verify-credentials-' . uniqid();

        $result = $this->sshkeys()->create($name, SshKeyGenerator::publicKey());

        if (! $result->success) {
            return CloudProviderResponse::failure(
                'Failed to verify Hetzner Cloud credentials'
            );
        }

        if ($result->data && isset($result->data->id)) {
            DeleteTestSshKeyJob::dispatch($this->token, (string) $result->data->id);
        }

        return CloudProviderResponse::success(
            'Hetzner Cloud credentials verified successfully'
        );
    }

    public function labels(): CloudLabelsInterface
    {
        return new Labels($this->token);
    }

    public function sshkeys(): CloudSshKeysInterface
    {
        return new SshKeys($this->token);
    }
}
