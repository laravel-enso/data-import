<?php

namespace LaravelEnso\DataImport\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use LaravelEnso\DataImport\Http\Requests\ValidateImport;
use LaravelEnso\DataImport\Models\Import;
use LaravelEnso\Helpers\Services\Obj;

class Store extends Controller
{
    public function __invoke(ValidateImport $request)
    {
        $params = $request->except(['import', 'type']);

        $import = Import::factory()->make([
            'type' => $request->get('type'),
            'params' => new Obj($params),
        ]);

        $rules = $import->template()->paramRules();

        Validator::make($params, $rules)->validate();

        return $import->upload($request->file('import'));
    }
}
