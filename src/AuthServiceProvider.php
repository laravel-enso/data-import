<?php

namespace LaravelEnso\DataImport;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use LaravelEnso\DataImport\App\Models\DataImport;
use LaravelEnso\DataImport\App\Policies\DataImport as Policy;

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
