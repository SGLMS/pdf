<?php

namespace Sglms\Pdf\Facades;

use Illuminate\Support\Facades\Facade;

class Pdf extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'pdf-service';
    }
}