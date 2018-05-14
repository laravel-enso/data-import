<?php

Route::middleware(['web', 'auth', 'core'])
    ->namespace('LaravelEnso\DataImport\app\Http\Controllers')
    ->prefix('api/import')->as('import.')
    ->group(function () {
        Route::get('initTable', 'DataImportTableController@init')
            ->name('initTable');
        Route::get('getTableData', 'DataImportTableController@data')
            ->name('getTableData');
        Route::get('exportExcel', 'DataImportTableController@excel')
            ->name('exportExcel');

        Route::get('', 'DataImportController@index')
            ->name('index');
        Route::delete('{dataImport}', 'DataImportController@destroy')
            ->name('destroy');
        Route::post('run/{type}', 'DataImportController@store')
            ->name('run');
        Route::get('download/{dataImport}', 'DataImportController@download')
            ->name('download');
        Route::get('getSummary/{dataImport}', 'DataImportController@summary')
            ->name('getSummary');

        Route::get('getTemplate/{type}', 'ImportTemplateController@template')
            ->name('getTemplate');
        Route::post('uploadTemplate/{type}', 'ImportTemplateController@store')
            ->name('uploadTemplate');
        Route::delete('deleteTemplate/{template}', 'ImportTemplateController@destroy')
            ->name('deleteTemplate');
        Route::get('downloadTemplate/{template}', 'ImportTemplateController@show')
            ->name('downloadTemplate');
    });
