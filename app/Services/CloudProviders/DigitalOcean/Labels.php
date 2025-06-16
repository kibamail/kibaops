<?php

namespace App\Services\CloudProviders\DigitalOcean;

use App\Contracts\CloudLabelsInterface;
use App\Services\CloudProviders\CloudProviderResponse;

class Labels implements CloudLabelsInterface
{
    public function create(string $key, string $value): CloudProviderResponse
    {
        return CloudProviderResponse::success('Label created successfully');
    }

    public function update(string $key, string $value): CloudProviderResponse
    {
        return CloudProviderResponse::success('Label updated successfully');
    }

    public function delete(string $key): CloudProviderResponse
    {
        return CloudProviderResponse::success('Label deleted successfully');
    }
}
