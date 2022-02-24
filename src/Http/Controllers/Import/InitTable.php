<?php

namespace LaravelEnso\DataImport\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\Tables\Builders\DataImport;
use LaravelEnso\Tables\Traits\Init;

class InitTable extends Controller
{
    use Init;

    protected string $tableClass = DataImport::class;
}
