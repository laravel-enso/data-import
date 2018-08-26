<?php

namespace LaravelEnso\DataImport\App\Http\Controllers;

use App\Http\Controllers\Controller;
use LaravelEnso\DataImport\app\Enums\ImportTypes;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Http\Requests\ValidateImportRequest;

class DataImportController extends Controller
{
    public function index()
    {
        $types = new ImportTypes();

        return ['importTypes' => $types::select()];
    }

    public function summary(DataImport $dataImport)
    {
        return $dataImport->summary();
    }

    public function store(ValidateImportRequest $request, string $type, DataImport $import)
    {
        return $import->store($request->file('import'), $type);
    }

    public function download(DataImport $dataImport)
    {
        return $dataImport->download();
    }

    public function destroy(DataImport $dataImport)
    {
        $dataImport->delete();

        return [
            'message' => __('The import record was successfully deleted'),
        ];
    }
}
