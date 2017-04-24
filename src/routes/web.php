<?php

Route::group(['namespace' => 'LaravelEnso\DataImport\app\Http\Controllers',
	'prefix' => 'import', 'as' => 'import.', 'middleware' => ['web', 'auth', 'core']], function () {
	Route::get('', 'DataImportController@index')->name('index');
	Route::get('getImportData', 'DataImportController@getImportData')->name('getImportData');
	Route::post('run', 'DataImportController@run')->name('run');
	Route::delete('{dataImport}', 'DataImportController@destroy')->name('destroy');
	Route::get('download/{dataImport}', 'DataImportController@download')->name('download');
	Route::get('initTable', 'DataImportController@initTable')->name('initTable');
	Route::get('getTableData', 'DataImportController@getTableData')->name('getTableData');
	Route::get('getSummary/{dataImport}', 'DataImportController@getSummary')->name('getSummary');
	Route::get('getTemplate/{type}', 'ImportTemplateController@getTemplate')->name('getTemplate');
	Route::post('uploadTemplate', 'ImportTemplateController@upload')->name('uploadTemplate');
	Route::delete('deleteTemplate/{template}', 'ImportTemplateController@destroy')->name('deleteTemplate');
	Route::get('downloadTemplate/{template}', 'ImportTemplateController@download')->name('downloadTemplate');
});