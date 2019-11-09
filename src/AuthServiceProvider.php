<?php

namespace LaravelEnso\DataImport;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Policies\Policy;

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
