<?php

Route::middleware(['auth:api', 'api', 'core'])
    ->namespace('LaravelEnso\DataImport\app\Http\Controllers')
    ->prefix('api/import')->as('import.')
    ->group(function () {
        Route::get('initTable', 'DataImportTableController@initTable')
            ->name('initTable');
        Route::get('getTableData', 'DataImportTableController@getTableData')
            ->name('getTableData');
        Route::get('exportExcel', 'DataImportTableController@exportExcel')
            ->name('exportExcel');

        Route::get('', 'DataImportController@index')
            ->name('index');
        Route::delete('{dataImport}', 'DataImportController@destroy')
            ->name('destroy');
        Route::post('run/{type}', 'DataImportController@store')
            ->name('run');
        Route::get('download/{dataImport}', 'DataImportController@download')
            ->name('download');
        Route::get('getSummary/{dataImport}', 'DataImportController@getSummary')
            ->name('getSummary');

        Route::get('getTemplate/{type}', 'ImportTemplateController@getTemplate')
            ->name('getTemplate');
        Route::post('uploadTemplate/{type}', 'ImportTemplateController@store')
            ->name('uploadTemplate');
        Route::delete('deleteTemplate/{template}', 'ImportTemplateController@destroy')
            ->name('deleteTemplate');
        Route::get('downloadTemplate/{template}', 'ImportTemplateController@show')
            ->name('downloadTemplate');
    });
