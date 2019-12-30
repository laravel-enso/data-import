<?php

namespace LaravelEnso\DataImport\App\Http\Controllers\Template;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\App\Models\ImportTemplate;

class Show extends Controller
{
    public function __invoke(string $type)
    {
        return ImportTemplate::whereType($type)->first();
    }
}
