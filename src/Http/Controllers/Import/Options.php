<?php

namespace LaravelEnso\DataImport\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\Services\Options as Service;

class Options extends Controller
{
    public function __invoke(Service $service)
    {
        return $service();
    }
}
