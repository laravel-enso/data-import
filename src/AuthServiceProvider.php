<?php

namespace LaravelEnso\DataImport;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use LaravelEnso\DataImport\Models\Import;
use LaravelEnso\DataImport\Policies\Policy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Import::class => Policy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
