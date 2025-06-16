<?php

namespace App\Providers;

use App\Services\HetznerCloudService;
use Illuminate\Support\ServiceProvider;

class HetznerCloudServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('hetzner-cloud', function ($app, $parameters) {
            // In testing, check if there's a mock instance bound
            if ($app->environment('testing') && $app->bound('hetzner-cloud-mock')) {
                return $app->make('hetzner-cloud-mock');
            }

            $token = $parameters['token'] ?? 'dummy-token';

            return new HetznerCloudService($token);
        });
    }

    public function boot()
    {
        //
    }
}
