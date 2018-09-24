<?php

namespace LaravelEnso\DataImport\App\Http\Controllers;

use Illuminate\Routing\Controller;
use LaravelEnso\VueDatatable\app\Traits\Excel;
use LaravelEnso\VueDatatable\app\Traits\Datatable;
use LaravelEnso\DataImport\app\Tables\Builders\DataImportTable;

class DataImportTableController extends Controller
{
    use Datatable, Excel;

    protected $tableClass = DataImportTable::class;
}
