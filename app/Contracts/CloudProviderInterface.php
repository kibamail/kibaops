<?php

namespace App\Contracts;

interface CloudProviderInterface
{
    /**
     * Verify that the provided credentials are valid for this cloud provider.
     * This method should test both read and write access to ensure the
     * credentials have sufficient permissions for our operations.
     *
     * @param array $credentials Array of credential values in the order defined by credentialFields()
     */
    public function verify(array $credentials): bool;
}
