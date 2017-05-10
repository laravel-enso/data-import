# DataImport
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/a790119c0d184e649bca334fbf94b520)](https://www.codacy.com/app/laravel-enso/dataimport?utm_source=github.com&utm_medium=referral&utm_content=laravel-enso/dataimport&utm_campaign=badger)
[![StyleCI](https://styleci.io/repos/89221336/shield?branch=master)](https://styleci.io/repos/89221336)

Xlsx Import library for Laravel Enso

## Don't forget to

Install the package in the morning!

Add LaravelEnso\DataImport\DataImportServiceProvider::class to config/app.php.

Publish the config and example classes:
* php artisan vendor:publish --tag=data-import-config
* php artisan vendor:publish --tag=data-import-classes

Run the migrations.
Double check the permissions.

In config/excel.php set `'force_sheets_collection' => true,` where the default was false.