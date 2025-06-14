<?php

namespace App\Services\Vault;

use AlexTartan\GuzzlePsr18Adapter\Client as GuzzleClient;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\Uri;
use Vault\AuthenticationStrategies\AppRoleAuthenticationStrategy;
use Vault\Client;

class VaultWritesClient extends Client
{
    private string $basePath;

    public function __construct(array $config, string $basePath)
    {
        parent::__construct(
            new Uri($config['address']),
            new GuzzleClient,
            new RequestFactory,
            new StreamFactory
        );

        $this->setAuthenticationStrategy(
            new AppRoleAuthenticationStrategy(
                $config['write']['role'],
                $config['write']['secret']
            )
        )->authenticate();

        $this->basePath = $basePath;
    }

    public function store(string $path, string|array $data)
    {
        $path = $this->basePath.'/'.$path;

        parent::write($path, [
            'data' => [
                'value' => $data,
            ],
        ]);

        return $path;
    }

    /**
     * Remove a secret from Vault at the specified path.
     * This method deletes the secret permanently from the Vault
     * storage and is used for cleanup operations.
     */
    public function remove(string $path): void
    {
        $path = $this->basePath.'/'.$path;
        parent::delete($path);
    }
}
