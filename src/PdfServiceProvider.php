<?php

namespace Sglms\Pdf;

use Illuminate\Support\ServiceProvider;
use Sglms\Pdf\PdfService;

/**
 * PdfServiceProvider
 *
 * @category Library
 * @package  Sglms/Pdf
 * @author   James <james@sglms.com>
 * @license  https://opensource.org/license/MIT MIT
 * @link     https://sglms.com/
 */
class PdfServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(
            'pdf-service',
            function ($app) {
                return new PdfService();
            }
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
