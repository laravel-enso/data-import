<?php

Route::middleware(['web', 'auth', 'core'])
    ->namespace('LaravelEnso\DataImport\app\Http\Controllers')
    ->prefix('api/import')->as('import.')
    ->group(function () {
        Route::get('initTable', 'DataImportTableController@init')
            ->name('initTable');
        Route::get('tableData', 'DataImportTableController@data')
            ->name('tableData');
        Route::get('exportExcel', 'DataImportTableController@excel')
            ->name('exportExcel');

        Route::get('', 'DataImportController@index')
            ->name('index');
        Route::delete('{dataImport}', 'DataImportController@destroy')
            ->name('destroy');
        Route::post('store', 'DataImportController@store')
            ->name('store');
        Route::get('download/{dataImport}', 'DataImportController@show')
            ->name('download');
        Route::get('downloadRejected/{rejectedImport}', 'RejectedImportController')
            ->name('downloadRejected');

        Route::get('template/{type}', 'ImportTemplateController@template')
            ->name('template');
        Route::post('uploadTemplate', 'ImportTemplateController@store')
            ->name('uploadTemplate');
        Route::delete('deleteTemplate/{importTemplate}', 'ImportTemplateController@destroy')
            ->name('deleteTemplate');
        Route::get('downloadTemplate/{importTemplate}', 'ImportTemplateController@show')
            ->name('downloadTemplate');
    });
