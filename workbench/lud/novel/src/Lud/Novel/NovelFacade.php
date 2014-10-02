<?php namespace Lud\Novel;

use Illuminate\Support\Facades\Facade;

class NovelFacade extends Facade {

    protected static function getFacadeAccessor() { return 'novel'; }

}
