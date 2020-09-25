<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Deepstack extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'deepstack';
    }
}
