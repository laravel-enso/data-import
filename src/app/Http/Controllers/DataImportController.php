<?php

namespace LaravelEnso\DataImport\App\Http\Controllers;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\app\Enums\Statuses;
use LaravelEnso\DataImport\app\Enums\ImportTypes;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Exceptions\ProcessingInProgress;
use LaravelEnso\DataImport\app\Http\Requests\ValidateImportRequest;

class DataImportController extends Controller
{
    public function index()
    {
        $types = new ImportTypes();

        return ['importTypes' => $types::select()];
    }

    public function store(ValidateImportRequest $request, DataImport $dataImport)
    {
        $dataImport->type = $request->get('type');

        return $dataImport->run($request->file('import'));
    }

    public function show(DataImport $dataImport)
    {
        return $dataImport->download();
    }

    public function destroy(DataImport $dataImport)
    {
        if ($dataImport->status !== Statuses::Finalized) {
            throw new ProcessingInProgress(
                __('The import is currently running and cannot be deleted')
            );
        }

        $dataImport->delete();

        return [
            'message' => __('The import record was successfully deleted'),
        ];
    }
}
