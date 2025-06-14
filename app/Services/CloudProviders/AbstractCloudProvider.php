<?php

namespace App\Services\CloudProviders;

use App\Contracts\CloudProviderInterface;
use Illuminate\Http\Client\Factory as HttpClient;

abstract class AbstractCloudProvider implements CloudProviderInterface
{
    protected HttpClient $http;

    /**
     * Create a new cloud provider instance with the HTTP client.
     */
    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    /**
     * Verify that the provided credentials are valid for this cloud provider.
     * This method must be implemented by each specific provider class.
     *
     * @param array $credentials Array of credential values in the order defined by credentialFields()
     */
    abstract public function verify(array $credentials): bool;

    /**
     * Make an HTTP request to the cloud provider's API and return success status.
     * This helper method handles different HTTP methods and catches exceptions
     * to provide a simple boolean response for credential verification.
     */
    protected function makeRequest(string $method, string $url, array $options = []): bool
    {
        try {
            if ($method === 'get') {
                $response = $this->http->withHeaders($options['headers'] ?? [])->get($url);
            } else {
                $response = $this->http->{$method}($url, $options);
            }

            return $response->successful();
        } catch (\Exception) {
            return false;
        }
    }
}
