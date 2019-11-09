<?php

namespace LaravelEnso\DataImport\App\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\app\Tables\Builders\DataImportTable;
use LaravelEnso\Tables\app\Traits\Data;

class TableData extends Controller
{
    use Data;

    protected $tableClass = DataImportTable::class;
}
