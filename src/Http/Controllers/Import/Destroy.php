<?php

namespace LaravelEnso\DataImport\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\Models\DataImport;

class Destroy extends Controller
{
    public function __invoke(DataImport $import)
    {
        $import->delete();

        return ['message' => __('The import record was successfully deleted')];
    }
}
