<?php

namespace LaravelEnso\DataImport;

use LaravelEnso\DataImport\Enums\CssClasses;
use LaravelEnso\DataImport\Enums\Statuses;
use LaravelEnso\Enums\EnumServiceProvider as ServiceProvider;

class EnumServiceProvider extends ServiceProvider
{
    public $register = [
        'importCssClasses' => CssClasses::class,
        'importStatuses' => Statuses::class,
    ];
}
