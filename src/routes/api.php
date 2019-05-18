<?php

Route::middleware(['web', 'auth', 'core'])
    ->namespace('LaravelEnso\DataImport\app\Http\Controllers')
    ->prefix('api/import')->as('import.')
    ->group(function () {
        Route::namespace('Import')
            ->group(function () {
                Route::get('', 'Index')->name('index');
                Route::delete('{dataImport}', 'Destroy')->name('destroy');
                Route::post('store', 'Store')->name('store');
                Route::get('download/{dataImport}', 'Download')->name('download');

                Route::get('initTable', 'InitTable')->name('initTable');
                Route::get('tableData', 'TableData')->name('tableData');
                Route::get('exportExcel', 'ExportExcel')->name('exportExcel');
            });

        Route::namespace('Rejected')
            ->group(function () {
                Route::get('downloadRejected/{rejectedImport}', 'Download')->name('downloadRejected');
            });

        Route::namespace('Template')
            ->group(function () {
                Route::get('template/{type}', 'Show')->name('template');
                Route::post('uploadTemplate', 'Store')->name('uploadTemplate');
                Route::delete('deleteTemplate/{importTemplate}', 'Destroy')->name('deleteTemplate');
                Route::get('downloadTemplate/{importTemplate}', 'Download')->name('downloadTemplate');
            });
    });
