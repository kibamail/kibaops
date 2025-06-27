<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class HetznerCloud extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'hetzner-cloud';
    }
}
