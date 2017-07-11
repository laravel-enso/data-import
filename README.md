<!--h-->
# DataImport

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/b169a2f09f864cd5b274ce63008f04b9)](https://www.codacy.com/app/laravel-enso/DataImport?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=laravel-enso/DataImport&amp;utm_campaign=Badge_Grade)
[![StyleCI](https://styleci.io/repos/89221336/shield?branch=master)](https://styleci.io/repos/89221336)
[![License](https://poser.pugx.org/laravel-enso/dataimport/license)](https://https://packagist.org/packages/laravel-enso/dataimport)
[![Total Downloads](https://poser.pugx.org/laravel-enso/dataimport/downloads)](https://packagist.org/packages/laravel-enso/dataimport)
[![Latest Stable Version](https://poser.pugx.org/laravel-enso/dataimport/version)](https://packagist.org/packages/laravel-enso/dataimport)
<!--/h-->

Excel Importer dependency for [Laravel Enso](https://github.com/laravel-enso/Enso).

[![Watch the demo](https://laravel-enso.github.io/dataimport/screenshots/Selection_006_thumb.png)](https://laravel-enso.github.io/dataimport/videos/demo_01.webm)
<sup>click on the photo to view a short demo in compatible browsers</sup>

[![Screenshot](https://laravel-enso.github.io/dataimport/screenshots/Selection_007_thumb.png)](https://laravel-enso.github.io/dataimport/screenshots/Selection_007.png)


### Features

- imports `xlsx` files into the application using the minimum required custom logic
- import types are defined in the package configuration
- each import type can be validated against required columns, sheets, data types and more
- the laravel validation is used for maximum reuse of existing mechanisms while custom validators can be added when necessary
- an example import type is included in the package
- uses `[Laravel Excel](https://github.com/Maatwebsite/Laravel-Excel)` for reading the `xlsx` file

### Installation steps

1. Add the `LaravelEnso\DataImport\DataImportServiceProvider::class` provider to `config/app.php`.

2. Run the migrations.

3. Publish the configuration and example classes:
    * `php artisan vendor:publish --tag=dataimport-config`
    * `php artisan vendor:publish --tag=dataimport-classes`

4. Double check the permissions.

5. In `config/excel.php` set `'force_sheets_collection' => true,` where the default was false.

### Publishes

- `php artisan vendor:publish --tag=dataimport-config` - configuration files
- `php artisan vendor:publish --tag=dataimport-classes` - example import

<!--h-->
### Contributions

are welcome. Pull requests are great, but issues are good too.

### License

This package is released under the MIT license.
<!--/h-->