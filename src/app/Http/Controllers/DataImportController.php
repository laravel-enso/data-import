<?php

namespace LaravelEnso\DataImport\App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use LaravelEnso\DataImport\app\Enums\ImportTypes;
use LaravelEnso\DataImport\app\Models\DataImport;

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

    public function store(Request $request, string $type)
    {
        return DataImport::store($request->allFiles(), $type);
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
