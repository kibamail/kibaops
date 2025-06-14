<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Vault Read Access Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines the role and secret credentials used for read-only access to HashiCorp Vault.
    | The role ID should be predetermined to ensure consistent access across different environments.
    | Deployments require fixed credentials that don't change regardless of script location.
    |
    */

    'read' => [
        'role' => env('VAULT_READ_ROLE_ID', 'kibaops-reads'),
        'secret' => env('VAULT_READ_SECRET_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Vault Write Access Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines the role and secret credentials used for write access to HashiCorp Vault.
    | The role ID should be predetermined to ensure consistent access across different environments.
    | Deployments require fixed credentials that don't change regardless of script location.
    |
    */

    'write' => [
        'role' => env('VAULT_WRITE_ROLE_ID', 'kibaops-writes'),
        'secret' => env('VAULT_WRITE_SECRET_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Vault Server Address Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration specifies the base URL for connecting to the HashiCorp Vault server instance.
    | The address should include the protocol and port for proper communication with Vault.
    | Docker environments typically use localhost with port 8200 for external access.
    |
    */

    'address' => env('VAULT_ADDR', 'http://127.0.0.1:8200'),

];