<?php

namespace LaravelEnso\DataImport\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\Enums\ImportTypes;

class Index extends Controller
{
    public function __invoke()
    {
        return ['types' => ImportTypes::select()];
    }
}
