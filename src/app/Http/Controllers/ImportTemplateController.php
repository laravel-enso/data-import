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

    public function store(ValidateTemplateRequest $request, string $type, ImportTemplate $template)
    {
        return $template->store($request->file('template'), $type);
    }

    public function show(ImportTemplate $template)
    {
        return $template->download();
    }

    public function destroy(ImportTemplate $template)
    {
        $template->delete();

        return [
            'message' => __('The template was successfully deleted'),
        ];
    }
}
