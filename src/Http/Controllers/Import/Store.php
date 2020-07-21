<?php

namespace LaravelEnso\DataImport\Http\Controllers\Import;

use Illuminate\Routing\Controller;
use LaravelEnso\DataImport\Http\Requests\ValidateImportRequest;
use LaravelEnso\DataImport\Services\Import;

class Store extends Controller
{
    public function __invoke(ValidateImportRequest $request)
    {
        $import = new Import(
            $request->get('type'),
            $request->file('import'),
            $request->except(['import', 'type'])
        );

        return $import->handle()
            ->summary();
    }
}
