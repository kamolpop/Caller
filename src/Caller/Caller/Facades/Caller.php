<?php namespace Caller\Caller\Facades;

use Illuminate\Support\Facades\Facade;

class Caller extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'caller'; }

}