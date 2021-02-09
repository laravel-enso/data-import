<?php

namespace LaravelEnso\DataImport\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\Models\DataImport;

class Restart extends Controller
{
    public function __invoke(DataImport $import)
    {
        $import->restart()->import();

        return ['message' => __('The import was restarted')];
    }
}
