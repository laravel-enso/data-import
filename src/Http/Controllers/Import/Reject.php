<?php

namespace LaravelEnso\DataImport\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\Models\DataImport;

class Reject extends Controller
{
    public function __invoke(DataImport $dataImport)
    {
        $dataImport->reject();

        return ['message' => __('The import was rejected successfully')];
    }
}
