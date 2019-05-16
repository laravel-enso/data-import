<?php

namespace LaravelEnso\DataImport\App\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Http\Requests\ValidateImportRequest;

class Store extends Controller
{
    public function __invoke(ValidateImportRequest $request, DataImport $dataImport)
    {
        $dataImport->type = $request->get('type');

        return $dataImport->handle(
            $request->file('import'),
            $request->except(['import', 'type'])
        );
    }
}
