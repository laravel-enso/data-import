<?php

namespace LaravelEnso\DataImport\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use LaravelEnso\DataImport\app\Classes\Import;
use LaravelEnso\DataImport\app\DataTable\DataImportTableStructure;
use LaravelEnso\DataImport\app\Enums\DataImportTypesEnum;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataTable\app\Traits\DataTable;
use LaravelEnso\FileManager\Classes\FileManager;
use LaravelEnso\Select\app\Traits\SelectListBuilder;

class DataImportController extends Controller
{
    use DataTable, SelectListBuilder;

    protected $tableStructureClass = DataImportTableStructure::class;
    private $fileManager;

    public function __construct()
    {
        $this->fileManager = new FileManager(config('laravel-enso.paths.imports'), config('laravel-enso.paths.temp'));
    }

    public function getTableQuery()
    {
        return DataImport::select(\DB::raw('
                data_imports.id as DT_RowId,
                data_imports.type,
                data_imports.original_name,
                data_imports.comment,
                data_imports.created_at,
                concat(users.first_name, " ", users.last_name) as created_by
            '))->join('users', 'data_imports.created_by', '=', 'users.id');
    }

    public function index()
    {
        $importTypes = $this->buildSelectList((new DataImportTypesEnum())->getData());

        return view('laravel-enso/data-import::dataImport.index', compact('importTypes'));
    }

    public function getSummary(DataImport $dataImport)
    {
        return [
            'summary' => $dataImport->summary,
        ];
    }

    public function run(Request $request, $type)
    {
        $uploadedFile = $request->allFiles()['file_0'];
        $this->checkIfFileIsValid($uploadedFile);
        $import = new Import($type, $uploadedFile);

        \DB::transaction(function () use ($import, $type, $uploadedFile) {
            $import->run();

            if ($import->isValid()) {
                $this->fileManager->startUpload([0 => $uploadedFile]);
                $dataImport = new DataImport($this->fileManager->getUploadedFiles()->first());
                $dataImport->type = $type;
                //$dataImport->comment = $comment;
                $dataImport->summary = $import->getSummary();
                $dataImport->save();
                $this->fileManager->commitUpload();
            }
        });

        return ['summary' => $import->getSummary()];
    }

    private function checkIfFileIsValid($file)
    {
        if (!$file->isValid()) {
            throw new \EnsoException('The file is not valid', 'error', []);
        }
    }

    public function download(DataImport $dataImport)
    {
        return $this->fileManager->download($dataImport->original_name, $dataImport->saved_name);
    }

    public function destroy(DataImport $dataImport)
    {
        \DB::transaction(function () use ($dataImport) {
            $dataImport->delete();
            $this->fileManager->delete($dataImport->saved_name);
        });

        return ['message' => 'Operation was succesful'];
    }
}
