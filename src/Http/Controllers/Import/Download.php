<?php

namespace LaravelEnso\DataImport\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\Models\Import;

class Download extends Controller
{
    public function __invoke(Import $import)
    {
        return $import->file->download();
    }
}
