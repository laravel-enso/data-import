<?php

namespace LaravelEnso\DataImport\App\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\Tables\app\Traits\Excel;
use LaravelEnso\DataImport\app\Tables\Builders\DataImportTable;

class ExportExcel extends Controller
{
    use Excel;

    protected $tableClass = DataImportTable::class;
}
