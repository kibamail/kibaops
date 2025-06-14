<?php

namespace App\Services\Vault;

use Vault\Client;
use Laminas\Diactoros\Uri;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\StreamFactory;
use AlexTartan\GuzzlePsr18Adapter\Client as GuzzleClient;
use Vault\AuthenticationStrategies\AppRoleAuthenticationStrategy;

class VaultService
{
    private array $config;
    private string $basePath = 'secrets/data';

    private VaultReadsClient $reads;
    private VaultWritesClient $writes;

    public function base(string $base)
    {
        $this->basePath = $base;

        return $this;
    }

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function reads() {

        if (isset($this->reads)) {
            return $this->reads;
        }

        $client = new VaultReadsClient($this->config, $this->basePath);

        $this->reads = $client;

        return $client;
    }

    public function writes() {
        if (isset($this->writes)) {
            return $this->writes;
        }

        $wirtes = new VaultWritesClient($this->config);

        $this->writes = $wirtes;

        return $wirtes;
    }
}
