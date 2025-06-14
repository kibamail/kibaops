<?php

namespace App\Services\Vault;

use Vault\Client;
use Laminas\Diactoros\Uri;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\StreamFactory;
use AlexTartan\GuzzlePsr18Adapter\Client as GuzzleClient;
use Vault\AuthenticationStrategies\AppRoleAuthenticationStrategy;

class VaultWritesClient extends Client
{
    public function __construct(array $config)
    {
        parent::__construct(
            new Uri($config['address']),
            new GuzzleClient(),
            new RequestFactory(),
            new StreamFactory()
        );

        $this->setAuthenticationStrategy(
            new AppRoleAuthenticationStrategy(
                $config['write']['role'],
                $config['write']['secret']
            )
        )->authenticate();
    }
}
