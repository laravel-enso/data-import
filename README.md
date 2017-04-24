# DataImport

Import library for xlsx

## Don't forget to

Install the package in the morning!

Add LaravelEnso\DataImport\DataImportServiceProvider::class to config/app.php.

Publish the config and example classes:
* php artisan vendor:publish --tag=data-import-config
* php artisan vendor:publish --tag=data-import-classes

Run the migrations.
Double check the permissions.

Either publish or manually copy the maatwebsite/excel package configuration
and set `'force_sheets_collection' => true,` where the default was false.