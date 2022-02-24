<?php

namespace LaravelEnso\DataImport\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\Models\Import;

class Destroy extends Controller
{
    public function __invoke(Import $import)
    {
        $import->delete();

        return ['message' => __('The import record was successfully deleted')];
    }
}
