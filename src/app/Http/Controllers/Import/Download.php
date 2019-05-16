<?php

namespace LaravelEnso\DataImport\App\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\app\Models\DataImport;

class Download extends Controller
{
    public function __invoke(DataImport $dataImport)
    {
        return $dataImport->download();
    }
}
