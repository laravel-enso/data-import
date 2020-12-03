<?php

namespace LaravelEnso\DataImport\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use LaravelEnso\DataImport\Enums\Statuses;
use LaravelEnso\DataImport\Http\Requests\ValidateImportRequest;
use LaravelEnso\DataImport\Models\DataImport;

class Store extends Controller
{
    public function __invoke(ValidateImportRequest $request)
    {
        $dataImport = DataImport::factory()->make([
            'type' => $request->get('type'),
            'params' => $request->except(['import', 'type']),
            'status' => Statuses::Waiting,
        ]);

        $rules = $dataImport->template()->paramRules();

        Validator::make($dataImport->params, $rules)->validate();

        return $dataImport->upload($request->file('import'));
    }
}
