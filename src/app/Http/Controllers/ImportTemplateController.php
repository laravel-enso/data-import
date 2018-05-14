<?php

namespace LaravelEnso\DataImport\App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use LaravelEnso\DataImport\app\Models\ImportTemplate;

class ImportTemplateController extends Controller
{
    public function template(string $type)
    {
        return ImportTemplate::whereType($type)
            ->first();
    }

    public function store(Request $request, string $type)
    {
        return ImportTemplate::store($request->allFiles(), $type);
    }

    public function show(ImportTemplate $template)
    {
        return $template->download();
    }

    public function destroy(ImportTemplate $template)
    {
        $template->delete();

        return ['message' => __('The template was successfully deleted')];
    }
}
