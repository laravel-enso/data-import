<?php

namespace LaravelEnso\DataImport\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use LaravelEnso\DataImport\App\Http\Services\DataImportService;
use LaravelEnso\DataImport\app\Models\DataImport;

class DataImportController extends Controller
{
    private $imports;

    public function __construct(Request $request)
    {
        $this->imports = new DataImportService($request);
    }

    public function index()
    {
        return $this->imports->index();
    }

    public function getSummary(DataImport $dataImport)
    {
        return $this->imports->getSummary($dataImport);
    }

    public function store(string $type)
    {
        return $this->imports->store($type);
    }

    public function show(DataImport $dataImport)
    {
        return $this->imports->show($dataImport);
    }

    public function destroy(DataImport $dataImport)
    {
        return $this->imports->destroy($dataImport);
    }
}
