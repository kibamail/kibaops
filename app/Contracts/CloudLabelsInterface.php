<?php

namespace App\Contracts;

use App\Services\CloudProviders\CloudProviderResponse;

interface CloudLabelsInterface
{
    /**
     * Create a new label in the cloud provider.
     *
     * @param  string  $key  The label key/name
     * @param  string  $value  The label value
     * @return CloudProviderResponse Response indicating success or failure
     */
    public function create(string $key, string $value): CloudProviderResponse;

    /**
     * Update an existing label in the cloud provider.
     *
     * @param  string  $key  The label key/name to update
     * @param  string  $value  The new label value
     * @return CloudProviderResponse Response indicating success or failure
     */
    public function update(string $key, string $value): CloudProviderResponse;

    /**
     * Delete a label from the cloud provider.
     *
     * @param  string  $key  The label key/name to delete
     * @return CloudProviderResponse Response indicating success or failure
     */
    public function delete(string $key): CloudProviderResponse;
}
