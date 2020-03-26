<?php

namespace LaravelEnso\DataImport\App\Http\Controllers\Template;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\App\Models\ImportTemplate;

class Destroy extends Controller
{
    public function __invoke(ImportTemplate $importTemplate)
    {
        $importTemplate->delete();

        return ['message' => __('The template was successfully deleted')];
    }
}
