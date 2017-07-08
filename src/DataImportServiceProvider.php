<?php

namespace LaravelEnso\DataImport;

use Illuminate\Support\ServiceProvider;
use LaravelEnso\DataImport\app\Classes\Template;

class DataImportServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishesAll();
        $this->loadDependencies();
    }

    private function publishesAll()
    {
        $this->publishes([
            __DIR__.'/config/importing.php' => config_path('importing.php'),
        ], 'dataimport-config');

        $this->publishes([
            __DIR__.'/config/importing.php' => config_path('importing.php'),
        ], 'enso-config');

        $this->publishes([
            __DIR__.'/resources/classes' => app_path(),
        ], 'dataimport-classes');
    }

    private function loadDependencies()
    {
        $this->mergeConfigFrom(__DIR__.'/config/importing.php', 'importing');
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'laravel-enso/data-import');
    }

    public function register()
    {
        $this->app->singleton('Template', function($app, $params)
        {
            return new Template($params['template']);
        });
    }
}
