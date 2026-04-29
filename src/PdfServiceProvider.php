<?php

namespace Sglms\Pdf;

use Illuminate\Support\ServiceProvider;

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
        $this->app->singleton(PdfService::class, static fn (): PdfService => new PdfService());
        $this->app->alias(PdfService::class, 'pdf');
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
