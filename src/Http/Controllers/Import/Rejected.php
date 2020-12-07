<?php

namespace LaravelEnso\DataImport\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\Models\RejectedImport;

class Rejected extends Controller
{
    public function __invoke(RejectedImport $rejected)
    {
        return $rejected->file->download();
    }
}
