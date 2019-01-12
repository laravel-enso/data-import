<?php

namespace LaravelEnso\DataImport\App\Http\Controllers;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\app\Models\ImportTemplate;
use LaravelEnso\DataImport\app\Http\Requests\ValidateTemplateRequest;

class ImportTemplateController extends Controller
{
    public function template(string $type)
    {
        return ImportTemplate::whereType($type)
            ->first();
    }

    public function store(ValidateTemplateRequest $request, ImportTemplate $importTemplate)
    {
        $importTemplate->type = $request->get('type');
        $importTemplate->store($request->file('template'));

        return $importTemplate;
    }

    public function show(ImportTemplate $importTemplate)
    {
        return $importTemplate->download();
    }

    public function destroy(ImportTemplate $importTemplate)
    {
        $importTemplate->delete();

        return [
            'message' => __('The template was successfully deleted'),
        ];
    }
}
