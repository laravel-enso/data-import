<?php

namespace LaravelEnso\DataImport\Http\Controllers\Rejected;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\Models\RejectedImport;

class Download extends Controller
{
    public function __invoke(RejectedImport $rejectedImport)
    {
        return $rejectedImport->download();
    }
}
