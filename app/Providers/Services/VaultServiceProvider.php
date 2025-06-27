<?php

namespace App\Providers\Services;

use App\Services\Vault\VaultService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class VaultServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(VaultService::class, function (Application $app) {
            return new VaultService(
                $app['config']['vault']
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
