<?php

namespace LaravelEnso\DataImport\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\Enums\Statuses;
use LaravelEnso\DataImport\Models\DataImport;

class Restart extends Controller
{
    public function __invoke(DataImport $import)
    {
        tap($import)->update(['status' => Statuses::Waiting])
            ->import();

        return ['message' => __('The import was restarted')];
    }
}
