<?php namespace Lud\Press;

use Illuminate\Support\Facades\Facade;

class PressFacade extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'press';
    }
}
