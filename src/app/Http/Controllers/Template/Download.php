<?php

namespace LaravelEnso\DataImport\App\Http\Controllers\Template;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\app\Models\ImportTemplate;

class Download extends Controller
{
    public function __invoke(ImportTemplate $importTemplate)
    {
        return $importTemplate->download();
    }
}
