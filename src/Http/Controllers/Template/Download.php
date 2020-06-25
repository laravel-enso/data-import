<?php

namespace LaravelEnso\DataImport\Http\Controllers\Template;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\Models\ImportTemplate;

class Download extends Controller
{
    public function __invoke(ImportTemplate $importTemplate)
    {
        return $importTemplate->download();
    }
}
