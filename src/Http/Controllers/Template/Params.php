<?php

namespace LaravelEnso\DataImport\Http\Controllers\Template;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\Models\ImportTemplate;

class Params extends Controller
{
    public function __invoke(string $type)
    {
        return ['params' => ImportTemplate::whereType($type)->first()];
    }
}
