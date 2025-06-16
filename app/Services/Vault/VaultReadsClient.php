<?php

namespace App\Services\Vault;

use AlexTartan\GuzzlePsr18Adapter\Client as GuzzleClient;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\Uri;
use Vault\AuthenticationStrategies\AppRoleAuthenticationStrategy;
use Vault\Client;

class VaultReadsClient extends Client
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
                $config['read']['role'],
                $config['read']['secret']
            )
        )->authenticate();

        $this->basePath = $basePath;
    }

    public function secret(string $path)
    {
        $path = $this->basePath . '/' . $path;

        return parent::read($path)->getData()['data']['value'];
    }
}
