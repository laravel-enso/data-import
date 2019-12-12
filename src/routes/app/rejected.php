<?php

Route::namespace('Rejected')
    ->group(function () {
        Route::get('downloadRejected/{rejectedImport}', 'Download')->name('downloadRejected');
    });
