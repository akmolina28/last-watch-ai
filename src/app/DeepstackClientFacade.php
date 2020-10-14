<?php

namespace App;

use Illuminate\Support\Facades\Facade;

class DeepstackClientFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'deepstack';
    }
}
