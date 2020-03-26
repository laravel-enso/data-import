<?php

namespace LaravelEnso\DataImport\App\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\App\Models\DataImport;

class Destroy extends Controller
{
    public function __invoke(DataImport $dataImport)
    {
        $dataImport->delete();

        return ['message' => __('The import record was successfully deleted')];
    }
}
