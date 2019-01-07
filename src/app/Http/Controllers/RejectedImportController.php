<?php

namespace LaravelEnso\DataImport\App\Http\Controllers;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\app\Models\RejectedImport;

class RejectedImportController extends Controller
{
    public function __invoke(RejectedImport $rejectedImport)
    {
        return $rejectedImport->download();
    }
}
