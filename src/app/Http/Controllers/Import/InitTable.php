<?php

namespace LaravelEnso\DataImport\App\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\Tables\app\Traits\Init;
use LaravelEnso\DataImport\app\Tables\Builders\DataImportTable;

class InitTable extends Controller
{
    use Init;

    protected $tableClass = DataImportTable::class;
}
