<?php

use Illuminate\Support\Facades\Route;
use LaravelEnso\DataImport\Http\Controllers\Template\Destroy;
use LaravelEnso\DataImport\Http\Controllers\Template\Download;
use LaravelEnso\DataImport\Http\Controllers\Template\Show;
use LaravelEnso\DataImport\Http\Controllers\Template\Store;

Route::get('template/{type}', Show::class)->name('template');
Route::post('uploadTemplate', Store::class)->name('uploadTemplate');
Route::delete('deleteTemplate/{importTemplate}', Destroy::class)->name('deleteTemplate');
Route::get('downloadTemplate/{importTemplate}', Download::class)->name('downloadTemplate');
