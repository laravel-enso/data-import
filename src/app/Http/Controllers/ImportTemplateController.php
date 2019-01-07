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

    public function store(ValidateTemplateRequest $request, ImportTemplate $template)
    {
        $template->type = $request->get('type');

        tap($template)->save()
            ->upload($request->file('template'));

        return $template;
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
