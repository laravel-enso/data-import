<?php

namespace LaravelEnso\DataImport;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use LaravelEnso\DataImport\Models\DataImport;
use LaravelEnso\DataImport\Models\RejectedImport;
use LaravelEnso\IO\Observers\IOObserver;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        DataImport::observe(IOObserver::class);

        $this->load()
            ->publishAssets()
            ->publishExamples()
            ->mapMorphs();
    }

    private function load()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        $this->mergeConfigFrom(__DIR__.'/../config/imports.php', 'enso.imports');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-enso/data-import');

        return $this;
    }

    private function publishAssets()
    {
        $this->publishes([
            __DIR__.'/../config' => config_path('enso'),
        ], ['data-import-config', 'enso-config']);

        $this->publishes([
            __DIR__.'/../database/factories' => database_path('factories'),
        ], ['data-import-factory', 'enso-factories']);

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-enso/data-import'),
        ], ['data-import-mail', 'enso-mail']);

        return $this;
    }

    private function publishExamples()
    {
        $stubPrefix = __DIR__.'/../stubs/';

        $stubs = (new Collection([
            'Imports/Importers/ExampleImporter',
            'Imports/Templates/exampleTemplate',
            'Imports/Validators/CustomValidator',
        ]))->reduce(fn ($stubs, $stub) => $stubs
            ->put("{$stubPrefix}{$stub}.stub", app_path("{$stub}.php")), new Collection());

        $this->publishes($stubs->all(), 'data-import-examples');

        return $this;
    }

    private function mapMorphs()
    {
        DataImport::morphMap();
        RejectedImport::morphMap();

        return $this;
    }
}
