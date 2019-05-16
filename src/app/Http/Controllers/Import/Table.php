<?php

namespace LaravelEnso\DataImport\App\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\Tables\app\Traits\Excel;
use LaravelEnso\Tables\app\Traits\Datatable;
use LaravelEnso\DataImport\app\Tables\Builders\DataImportTable;

class Table extends Controller
{
    use Datatable, Excel;

    protected $tableClass = DataImportTable::class;
}
