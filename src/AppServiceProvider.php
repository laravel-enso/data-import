<?php

namespace LaravelEnso\DataImport;

use Illuminate\Support\ServiceProvider;
use LaravelEnso\Core\app\Observers\IOObserver;
use LaravelEnso\DataImport\app\Models\DataImport;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        DataImport::observe(IOObserver::class);

        $this->loadDependencies()
            ->publishDependencies();
    }

    private function loadDependencies()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        $this->loadRoutesFrom(__DIR__.'/routes/api.php');

        $this->mergeConfigFrom(__DIR__.'/config/imports.php', 'imports');

        $this->loadViewsFrom(__DIR__.'/resources/views', 'laravel-enso/dataimport');

        return $this;
    }

    private function publishDependencies()
    {
        $this->publishes([
            __DIR__.'/config' => config_path('enso'),
        ], 'dataimport-config');

        $this->publishes([
            __DIR__.'/config' => config_path('enso'),
        ], 'enso-config');

        $this->publishes([
            __DIR__.'/../resources' => app_path(),
        ], 'dataimport-examples');

        $this->publishes([
            __DIR__.'/resources/js' => resource_path('js'),
        ], 'import-assets');

        $this->publishes([
            __DIR__.'/resources/js' => resource_path('js'),
        ], 'enso-assets');

        $this->publishes([
            __DIR__.'/resources/views' => resource_path('views/vendor/laravel-enso/dataimport'),
        ], 'dataimport-mail');

        $this->publishes([
            __DIR__.'/resources/views' => resource_path('views/vendor/laravel-enso/dataimport'),
        ], 'enso-mail');
    }

    public function register()
    {
        //
    }
}
