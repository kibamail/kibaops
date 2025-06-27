<?php

namespace App\Services\CloudProviders\Hetzner;

use App\Contracts\CloudLabelsInterface;
use App\Services\CloudProviders\CloudProviderResponse;

class Labels implements CloudLabelsInterface
{
    private ?string $token;

    public function __construct(?string $token = null)
    {
        $this->token = $token;
    }

    public function create(string $key, string $value): CloudProviderResponse
    {
        $hetznerService = app('hetzner-cloud', ['token' => $this->token]);
        $result = $hetznerService->createLabel($key, $value);

        if (! $result) {
            return CloudProviderResponse::failure('Failed to create label');
        }

        return CloudProviderResponse::success('Label created successfully');
    }

    public function update(string $key, string $value): CloudProviderResponse
    {
        $hetznerService = app('hetzner-cloud', ['token' => $this->token]);
        $result = $hetznerService->updateLabel($key, $value);

        if (! $result) {
            return CloudProviderResponse::failure('Failed to update label');
        }

        return CloudProviderResponse::success('Label updated successfully');
    }

    public function delete(string $key): CloudProviderResponse
    {
        $hetznerService = app('hetzner-cloud', ['token' => $this->token]);
        $result = $hetznerService->deleteLabel($key);

        if (! $result) {
            return CloudProviderResponse::failure('Failed to delete label');
        }

        return CloudProviderResponse::success('Label deleted successfully');
    }
}
