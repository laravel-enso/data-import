# DataImport

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/b169a2f09f864cd5b274ce63008f04b9)](https://www.codacy.com/app/laravel-enso/DataImport?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=laravel-enso/DataImport&amp;utm_campaign=Badge_Grade)
[![StyleCI](https://styleci.io/repos/89221336/shield?branch=master)](https://styleci.io/repos/89221336)
[![License](https://poser.pugx.org/laravel-enso/dataimport/license)](https://packagist.org/packages/laravel-enso/dataimport)
[![Total Downloads](https://poser.pugx.org/laravel-enso/dataimport/downloads)](https://packagist.org/packages/laravel-enso/dataimport)
[![Latest Stable Version](https://poser.pugx.org/laravel-enso/dataimport/version)](https://packagist.org/packages/laravel-enso/dataimport)

Incredibly powerful, efficient, unlimited number of rows, queues based Excel importer dependency for [Laravel Enso](https://github.com/laravel-enso/Enso).

[![Watch the demo](https://laravel-enso.github.io/dataimport/screenshots/bulma_006_thumb.png)](https://laravel-enso.github.io/dataimport/videos/bulma_demo_01.mp4)


<sup>click on the photo to view a short demo in compatible browsers</sup>

[![Screenshot](https://laravel-enso.github.io/dataimport/screenshots/bulma_007_thumb.png)](https://laravel-enso.github.io/dataimport/screenshots/bulma_007.png)


### Features

- allows the import of **big** files with the number of rows only limited by the xlsx file format, 
by splitting the data in chunks and handling them on multiple queues
- uses JSON templates to configure `xlsx` file imports into the application, with minimum custom logic
- import types are defined in the package configuration
- each import type can be validated against required columns, sheets, data types and more
- the Laravel validation is utilized for maximum reuse of existing mechanisms while custom validators can be added when necessary
- an example import type is included by default in the package
- uses [Spout](https://github.com/box/spout) for reading the `xlsx` file
- uses Laravel's queueing system and its auto-balancing features for efficient asynchronous, parallel processing
- blocking file structure validation
- non blocking file contents validation 
- content import issues are made available in the rejected rows summary, a downloadable `xlsx` file with the same structure as the import file,
with an extra column (on each sheet) that will describe all the validation errors for each row
- features real time import progress reporting in the UI
- `before` and `after` hooks which are available during the importing process
- comes with an utility ExcelSeeder class, that can be used to seed your tables using data from excel files

### Configuration & Usage

Be sure to check out the full documentation for this package available at [docs.laravel-enso.com](https://docs.laravel-enso.com/packages/data-import.html)

### Contributions

are welcome. Pull requests are great, but issues are good too.

### License

This package is released under the MIT license.
