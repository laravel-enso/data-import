<?php

namespace LaravelEnso\DataImport\App\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\app\Tables\Builders\DataImportTable;
use LaravelEnso\Tables\app\Traits\Init;

class InitTable extends Controller
{
    use Init;

    protected $tableClass = DataImportTable::class;
}
