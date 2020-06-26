<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'auth', 'core'])
    ->namespace('LaravelEnso\DataImport\Http\Controllers')
    ->prefix('api/import')->as('import.')
    ->group(function () {
        require 'app/imports.php';
        require 'app/rejected.php';
        require 'app/template.php';
    });
