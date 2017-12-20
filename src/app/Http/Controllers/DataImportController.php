<?php

namespace LaravelEnso\DataImport\App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use LaravelEnso\DataImport\app\Enums\ImportTypes;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\App\Http\Services\DataImportService;

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

    public function store(Request $request, string $type, DataImportService $service)
    {
        return $service->store($request, $type);
    }

    public function download(DataImport $dataImport, DataImportService $service)
    {
        return $service->download($dataImport);
    }

    public function destroy(DataImport $dataImport, DataImportService $service)
    {
        return $service->destroy($dataImport);
    }
}
