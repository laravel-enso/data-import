<?php

namespace LaravelEnso\DataImport;

use LaravelEnso\DataImport\app\Policies\Policy;
use LaravelEnso\DataImport\app\Models\DataImport;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        DataImport::class => Policy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
