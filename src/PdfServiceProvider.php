<?php

namespace Sglms\Pdf;

use Illuminate\Support\ServiceProvider;
use Sglms\Pdf\PdfService;

class PdfServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('pdf-service', function ($app) {
            return new PdfService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
