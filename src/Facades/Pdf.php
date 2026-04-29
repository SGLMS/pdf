<?php

namespace Sglms\Pdf\Facades;

use Illuminate\Support\Facades\Facade;
use Sglms\Pdf\PdfService;

class Pdf extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return PdfService::class;
    }
}