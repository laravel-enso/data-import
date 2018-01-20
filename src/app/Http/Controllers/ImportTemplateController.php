<?php

namespace LaravelEnso\DataImport\App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use LaravelEnso\DataImport\app\Handlers\Storer;
use LaravelEnso\DataImport\app\Handlers\Presenter;
use LaravelEnso\DataImport\app\Handlers\Destroyer;
use LaravelEnso\DataImport\app\Models\ImportTemplate;

class ImportTemplateController extends Controller
{
    public function getTemplate(string $type)
    {
        $template = ImportTemplate::whereType($type)->first();

        return $template ?: new ImportTemplate();
    }

    public function store(Request $request, string $type)
    {
        return (new Storer($request->allFiles(), $type))->run();
    }

    public function show(ImportTemplate $template)
    {
        return (new Presenter($template))->download();
    }

    public function destroy(ImportTemplate $template)
    {
        (new Destroyer($template))->run();

        return ['message' => __(config('enso.labels.successfulOperation'))];
    }
}
