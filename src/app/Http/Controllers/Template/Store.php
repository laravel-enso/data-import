<?php

namespace LaravelEnso\DataImport\App\Http\Controllers\Template;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\app\Models\ImportTemplate;
use LaravelEnso\DataImport\app\Http\Requests\ValidateTemplateRequest;

class Store extends Controller
{
    public function __invoke(ValidateTemplateRequest $request, ImportTemplate $importTemplate)
    {
        $importTemplate->type = $request->get('type');
        $importTemplate->store($request->file('template'));

        return $importTemplate;
    }
}
