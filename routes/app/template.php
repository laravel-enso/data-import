<?php

use Illuminate\Support\Facades\Route;

Route::namespace('Template')
    ->group(function () {
        Route::get('template/{type}', 'Show')->name('template');
        Route::post('uploadTemplate', 'Store')->name('uploadTemplate');
        Route::delete('deleteTemplate/{importTemplate}', 'Destroy')->name('deleteTemplate');
        Route::get('downloadTemplate/{importTemplate}', 'Download')->name('downloadTemplate');
    });
