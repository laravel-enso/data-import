<?php

namespace LaravelEnso\DataImport\App\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\app\Enums\ImportTypes;

class Index extends Controller
{
    public function __invoke()
    {
        return ['importTypes' => ImportTypes::select()];
    }
}
