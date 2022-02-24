<?php

namespace LaravelEnso\DataImport\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\Tables\Builders\DataImport;
use LaravelEnso\Tables\Traits\Data;

class TableData extends Controller
{
    use Data;

    protected string $tableClass = DataImport::class;
}
