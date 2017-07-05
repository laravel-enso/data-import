<?php

namespace LaravelEnso\DataImport\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use LaravelEnso\DataImport\App\Http\Services\DataImportService;
use LaravelEnso\DataImport\app\Classes\Import;
use LaravelEnso\DataImport\app\DataTable\ImportTableStructure;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataTable\app\Traits\DataTable;

class DataImportController extends Controller
{
    use DataTable;

    protected $tableStructureClass = ImportTableStructure::class;

    private $import;

    public function __construct(Request $request)
    {
        $this->import = new DataImportService($request);
    }

    public function getTableQuery()
    {
        return $this->import->getTableQuery();
    }

    public function index()
    {
        return $this->import->index();
    }

    public function getSummary(DataImport $dataImport)
    {
        return $this->import->getSummary($dataImport);
    }

    public function run(string $type)
    {
        return $this->import->run($type);
    }

    public function download(DataImport $dataImport)
    {
        return $this->import->download($dataImport);
    }

    public function destroy(DataImport $dataImport)
    {
        return $this->import->destroy($dataImport);
    }
}
