<?php

namespace LaravelEnso\DataImport\App\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\Tables\app\Traits\Data;
use LaravelEnso\DataImport\app\Tables\Builders\DataImportTable;

class TableData extends Controller
{
    use Data;

    protected $tableClass = DataImportTable::class;
}
