<?php namespace Lud\Press;

use Illuminate\Support\Facades\Facade;

class SeoFacade extends Facade {

    protected static function getFacadeAccessor() { return 'press.seo'; }

}

