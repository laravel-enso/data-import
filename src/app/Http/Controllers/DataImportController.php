<?php

namespace LaravelEnso\DataImport\App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use LaravelEnso\DataImport\app\Enums\ImportTypes;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Handlers\Importer;
use LaravelEnso\DataImport\app\Handlers\Presenter;
use LaravelEnso\DataImport\app\Handlers\Destroyer;

class DataImportController extends Controller
{
    public function index()
    {
        $types = new ImportTypes();

        return ['importTypes' => $types::all()];
    }

    public function getSummary(DataImport $dataImport)
    {
        return json_encode($dataImport->summary);
    }

    public function store(Request $request, string $type)
    {
        return (new Importer($request->allFiles(), $type))->run();
    }

    public function download(DataImport $dataImport)
    {
        return (new Presenter($dataImport))->download();
    }

    public function destroy(DataImport $dataImport)
    {
        (new Destroyer($dataImport))->run();

        return ['message' => __(config('enso.labels.successfulOperation'))];
    }
}
