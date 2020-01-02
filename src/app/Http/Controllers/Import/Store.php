<?php

namespace LaravelEnso\DataImport\App\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\App\Http\Requests\ValidateImportRequest;
use LaravelEnso\DataImport\App\Models\DataImport;

class Store extends Controller
{
    public function __invoke(ValidateImportRequest $request)
    {
        $dataImport = factory(DataImport::class)
            ->make(['type' => $request->get('type')]);

        return $dataImport->handle(
            $request->file('import'),
            $request->except(['import', 'type'])
        );
    }
}
