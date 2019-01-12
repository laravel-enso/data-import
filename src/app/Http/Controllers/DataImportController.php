<?php

namespace LaravelEnso\DataImport\App\Http\Controllers;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\app\Enums\ImportTypes;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Http\Requests\ValidateImportRequest;

class DataImportController extends Controller
{
    public function index()
    {
        return ['importTypes' => ImportTypes::select()];
    }

    public function store(ValidateImportRequest $request, DataImport $dataImport)
    {
        $dataImport->type = $request->get('type');

        return $dataImport->run(
            $request->file('import'),
            $request->except(['import', 'type'])
        );
    }

    public function show(DataImport $dataImport)
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
