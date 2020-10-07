<?php

use Illuminate\Support\Facades\Route;
use LaravelEnso\DataImport\Http\Controllers\Rejected\Download;

Route::get('downloadRejected/{rejectedImport}', Download::class)->name('downloadRejected');
