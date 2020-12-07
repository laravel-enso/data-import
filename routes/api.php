<?php

use Illuminate\Support\Facades\Route;
use LaravelEnso\DataImport\Http\Controllers\Import\Cancel;
use LaravelEnso\DataImport\Http\Controllers\Import\Destroy;
use LaravelEnso\DataImport\Http\Controllers\Import\Download;
use LaravelEnso\DataImport\Http\Controllers\Import\ExportExcel;
use LaravelEnso\DataImport\Http\Controllers\Import\InitTable;
use LaravelEnso\DataImport\Http\Controllers\Import\Rejected;
use LaravelEnso\DataImport\Http\Controllers\Import\Restart;
use LaravelEnso\DataImport\Http\Controllers\Import\Show;
use LaravelEnso\DataImport\Http\Controllers\Import\Store;
use LaravelEnso\DataImport\Http\Controllers\Import\TableData;
use LaravelEnso\DataImport\Http\Controllers\Import\Template;

Route::middleware(['api', 'auth', 'core'])
    ->prefix('api/import')->as('import.')
    ->group(function () {
        Route::delete('{import}', Destroy::class)->name('destroy');
        Route::post('store', Store::class)->name('store');
        Route::get('download/{import}', Download::class)->name('download');

        Route::get('initTable', InitTable::class)->name('initTable');
        Route::get('tableData', TableData::class)->name('tableData');
        Route::get('exportExcel', ExportExcel::class)->name('exportExcel');

        Route::patch('{import}/cancel', Cancel::class)->name('cancel');
        Route::patch('{import}/restart', Restart::class)->name('restart');

        Route::get('{type}', Show::class)->name('show');

        Route::get('{type}/template', Template::class)->name('template');

        Route::get('{rejected}/rejected', Rejected::class)->name('rejected');
    });
