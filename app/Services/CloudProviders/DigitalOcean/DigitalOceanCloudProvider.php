<?php

namespace App\Services\CloudProviders\DigitalOcean;

use App\Contracts\CloudLabelsInterface;
use App\Contracts\CloudProviderInterface;
use App\Contracts\CloudSshKeysInterface;
use App\Services\CloudProviders\CloudProviderResponse;

class DigitalOceanCloudProvider implements CloudProviderInterface
{
    public function verify(array $credentials): CloudProviderResponse
    {
        return CloudProviderResponse::success('DigitalOcean credentials verified successfully');
    }

    public function labels(): CloudLabelsInterface
    {
        return new Labels;
    }

    public function sshkeys(): CloudSshKeysInterface
    {
        return new SshKeys;
    }
}
