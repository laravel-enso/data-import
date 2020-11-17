<?php

namespace LaravelEnso\DataImport\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\Http\Responses\Import;
use LaravelEnso\DataImport\Services\Template;
use LaravelEnso\DataImport\Services\Validators\Params\Structure;

class Show extends Controller
{
    public function __invoke(string $type)
    {
        $template = new Template($type);
        (new Structure($template))->validate();

        return new Import($template);
    }
}
