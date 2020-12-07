<?php

namespace LaravelEnso\DataImport\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\Models\DataImport;

class Download extends Controller
{
    public function __invoke(DataImport $import)
    {
        return $import->file->download();
    }
}
