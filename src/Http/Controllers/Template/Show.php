<?php

namespace LaravelEnso\DataImport\Http\Controllers\Template;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\Models\ImportTemplate;

class Show extends Controller
{
    public function __invoke(string $type)
    {
        return ['template' => ImportTemplate::whereType($type)->first()];
    }
}
