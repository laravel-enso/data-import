<?php

namespace LaravelEnso\DataImport;

use Illuminate\Support\ServiceProvider;
use LaravelEnso\DataImport\app\Classes\Template;
use Maatwebsite\Excel\ExcelServiceProvider;

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
            __DIR__.'/config' => config_path(),
        ], 'dataimport-config');

        $this->publishes([
            __DIR__.'/config' => config_path(),
        ], 'enso-config');

        $this->publishes([
            __DIR__.'/resources/classes' => app_path(),
        ], 'dataimport-classes');

        $this->publishes([
            __DIR__.'/resources/assets/images' => resource_path('assets/images'),
        ], 'dataimport-logo');
    }

    private function loadDependencies()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->mergeConfigFrom(__DIR__.'/config/importing.php', 'importing');
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'laravel-enso/data-import');
    }

    public function register()
    {
        $this->app->singleton('Template', function ($app, $params) {
            return new Template($params['template']);
        });

        $this->app->register(ExcelServiceProvider::class);
    }
}
