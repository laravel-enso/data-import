<?php

namespace LaravelEnso\DataImport;

use Illuminate\Support\ServiceProvider;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\IO\app\Observers\IOObserver;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        DataImport::observe(IOObserver::class);

        $this->load()
            ->publish();
    }

    private function load()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        $this->loadRoutesFrom(__DIR__.'/routes/api.php');

        $this->mergeConfigFrom(__DIR__.'/config/imports.php', 'imports');

        $this->loadViewsFrom(__DIR__.'/resources/views', 'laravel-enso/data-import');

        return $this;
    }

    private function publish()
    {
        $this->publishConfig()
            ->publishFactories()
            ->publishExamples()
            ->publishEmailViews();
    }

    private function publishConfig()
    {
        $this->publishes([
            __DIR__.'/config' => config_path('enso'),
        ], 'data-import-config');

        $this->publishes([
            __DIR__.'/config' => config_path('enso'),
        ], 'enso-config');

        return $this;
    }

    private function publishFactories()
    {
        $this->publishes([
            __DIR__.'/database/factories' => database_path('factories'),
        ], 'data-import-factory');

        $this->publishes([
            __DIR__.'/database/factories' => database_path('factories'),
        ], 'enso-factories');

        return $this;
    }

    private function publishEmailViews()
    {
        $this->publishes([
            __DIR__.'/resources/views' => resource_path('views/vendor/laravel-enso/data-import'),
        ], 'data-import-mail');

        $this->publishes([
            __DIR__.'/resources/views' => resource_path('views/vendor/laravel-enso/data-import'),
        ], 'enso-mail');

        return $this;
    }

    private function publishExamples()
    {
        $this->publishes([
            __DIR__.'/../stubs/Imports/Importers/ExampleImporter.stub' => app_path('Imports/Importers/ExampleImporter.php'),
            __DIR__.'/../stubs/Imports/Templates/exampleTemplate.stub' => app_path('Imports/Templates/exampleTemplate.json'),
            __DIR__.'/../stubs/Imports/Validators/CustomValidator.stub' => app_path('Imports/Validators/CustomValidator.php'),
        ], 'data-import-examples');

        return $this;
    }
}
