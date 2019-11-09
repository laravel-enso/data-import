<?php

namespace LaravelEnso\DataImport\App\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\app\Http\Requests\ValidateImportRequest;
use LaravelEnso\DataImport\app\Models\DataImport;

class Store extends Controller
{
    public function __invoke(ValidateImportRequest $request, DataImport $dataImport)
    {
        $dataImport = factory(DataImport::class)
            ->make(['type' => $request->get('type')]);

        return $dataImport->handle(
            $request->file('import'),
            $request->except(['import', 'type'])
        );
    }
}
