<?php

namespace LaravelEnso\DataImport\App\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\app\Tables\Builders\DataImportTable;
use LaravelEnso\Tables\app\Traits\Excel;

class ExportExcel extends Controller
{
    use Excel;

    protected $tableClass = DataImportTable::class;
}
