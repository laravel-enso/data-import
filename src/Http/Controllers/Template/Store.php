<?php

namespace LaravelEnso\DataImport\Http\Controllers\Template;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\Http\Requests\ValidateTemplateRequest;
use LaravelEnso\DataImport\Models\ImportTemplate;

class Store extends Controller
{
    public function __invoke(ValidateTemplateRequest $request, ImportTemplate $importTemplate)
    {
        $importTemplate->type = $request->get('type');
        $importTemplate->store($request->file('template'));

        return ['template' => $importTemplate];
    }
}
