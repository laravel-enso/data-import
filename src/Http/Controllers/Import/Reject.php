<?php

namespace LaravelEnso\DataImport\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\Enums\Statuses;
use LaravelEnso\DataImport\Models\DataImport;

class Reject extends Controller
{
    public function __invoke()
    {
        DataImport::inprogress()
            ->update(['status' => Statuses::Rejected]);

        return ['message' => __('Stuck imports were rejected successfully')];
    }
}
