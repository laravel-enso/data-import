<?php

namespace LaravelEnso\DataImport;

use Illuminate\Support\ServiceProvider;

class DataImportServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/importing.php' => config_path('importing.php'),
        ], 'data-import-config');

         $this->publishes([
            __DIR__.'/resources/classes' => app_path(),
        ], 'data-import-classes');

        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'laravel-enso/data-import');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
