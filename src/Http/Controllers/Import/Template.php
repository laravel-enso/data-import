<?php

namespace LaravelEnso\DataImport\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\Services\ImportTemplate;
use LaravelEnso\Excel\Services\ExcelExport;

class Template extends Controller
{
    public function __invoke(string $type)
    {
        (new ExcelExport(new ImportTemplate($type)))->inline();
    }
}
