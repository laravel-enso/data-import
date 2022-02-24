<?php

namespace LaravelEnso\DataImport\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\Tables\Builders\DataImport;
use LaravelEnso\Tables\Traits\Excel;

class ExportExcel extends Controller
{
    use Excel;

    protected string $tableClass = DataImport::class;
}
