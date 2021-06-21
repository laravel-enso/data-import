<?php

namespace LaravelEnso\DataImport\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use LaravelEnso\DataImport\Http\Requests\ValidateImportRequest;
use LaravelEnso\DataImport\Models\DataImport;

class Store extends Controller
{
    public function __invoke(ValidateImportRequest $request)
    {
        $params = $request->except(['import', 'type']);

        $dataImport = DataImport::factory()->make([
            'type' => $request->get('type'),
            'params' => new Obj($params),
        ]);

        $rules = $dataImport->template()->paramRules();

        Validator::make($params, $rules)->validate();

        return $dataImport->upload($request->file('import'));
    }
}
