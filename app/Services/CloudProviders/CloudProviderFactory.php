<?php

namespace App\Services\CloudProviders;

use App\Contracts\CloudProviderInterface;
use App\Enums\CloudProviderType;
use Illuminate\Http\Client\Factory as HttpClient;
use InvalidArgumentException;

class CloudProviderFactory
{
    private HttpClient $http;

    /**
     * Create a new cloud provider factory with the HTTP client dependency.
     */
    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    /**
     * Create a cloud provider instance based on the specified type.
     * This factory method instantiates the appropriate provider class
     * and injects the HTTP client for making API requests.
     */
    public function create(CloudProviderType $type): CloudProviderInterface
    {
        return match ($type) {
            CloudProviderType::HETZNER => new HetznerCloudProvider($this->http),
            CloudProviderType::DIGITAL_OCEAN => new DigitalOceanCloudProvider($this->http),
            default => throw new InvalidArgumentException("Cloud provider type '{$type->value}' is not implemented yet."),
        };
    }
}
