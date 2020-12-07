<?php

namespace LaravelEnso\DataImport\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\Services\Template;

class Show extends Controller
{
    public function __invoke(string $type)
    {
        return ['params' => (new Template($type))->params(false)];
    }
}
