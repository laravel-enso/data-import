<?php

use Illuminate\Support\Facades\Route;

Route::namespace('Import')
    ->group(function () {
        Route::get('', 'Index')->name('index');
        Route::delete('{dataImport}', 'Destroy')->name('destroy');
        Route::post('store', 'Store')->name('store');
        Route::get('download/{dataImport}', 'Download')->name('download');

        Route::get('initTable', 'InitTable')->name('initTable');
        Route::get('tableData', 'TableData')->name('tableData');
        Route::get('exportExcel', 'ExportExcel')->name('exportExcel');

        Route::patch('{dataImport}/cancel', 'Cancel')->name('cancel');

        Route::get('{type}', 'Show')->name('show');
    });
