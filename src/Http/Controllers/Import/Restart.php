<?php

namespace LaravelEnso\DataImport\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\Models\Import;

class Restart extends Controller
{
    public function __invoke(Import $import)
    {
        $import->restart();

        return ['message' => __('The import was restarted')];
    }
}
