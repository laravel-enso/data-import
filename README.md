# DataImport
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/b169a2f09f864cd5b274ce63008f04b9)](https://www.codacy.com/app/laravel-enso/DataImport?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=laravel-enso/DataImport&amp;utm_campaign=Badge_Grade)
[![StyleCI](https://styleci.io/repos/89221336/shield?branch=master)](https://styleci.io/repos/89221336)
[![Total Downloads](https://poser.pugx.org/laravel-enso/dataimport/downloads)](https://packagist.org/packages/laravel-enso/dataimport)
[![Latest Stable Version](https://poser.pugx.org/laravel-enso/dataimport/version)](https://packagist.org/packages/laravel-enso/dataimport)

Excel Importer

## Don't forget to

Add LaravelEnso\DataImport\DataImportServiceProvider::class to config/app.php.

Publish the config and example classes:
* php artisan vendor:publish --tag=data-import-config
* php artisan vendor:publish --tag=data-import-classes

Run the migrations.
Double check the permissions.

In config/excel.php set `'force_sheets_collection' => true,` where the default was false.