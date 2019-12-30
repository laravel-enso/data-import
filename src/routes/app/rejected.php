<?php

use Illuminate\Support\Facades\Route;

Route::namespace('Rejected')
    ->group(function () {
        Route::get('downloadRejected/{rejectedImport}', 'Download')->name('downloadRejected');
    });
