<?php

namespace LaravelEnso\DataImport\App\Http\Controllers\Rejected;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\app\Models\RejectedImport;

class Download extends Controller
{
    public function __invoke(RejectedImport $rejectedImport)
    {
        return $rejectedImport->download();
    }
}
