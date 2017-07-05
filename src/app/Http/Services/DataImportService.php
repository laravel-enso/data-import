<?php

namespace LaravelEnso\DataImport\App\Http\Services;

use Illuminate\Http\Request;
use LaravelEnso\DataImport\app\Classes\Importers\Importer;
use LaravelEnso\DataImport\app\Enums\ImportTypes;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\FileManager\Classes\FileManager;
use LaravelEnso\Select\app\Traits\SelectListBuilder;

class DataImportService
{
    use SelectListBuilder;

    private $request;
    private $fileManager;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->fileManager = new FileManager(config('laravel-enso.paths.imports'), config('laravel-enso.paths.temp'));
        $this->fileManager->setValidExtensions(['xls', 'xlsx']);
    }

    public function getTableQuery()
    {
        return DataImport::select(\DB::raw('data_imports.id as DT_RowId, data_imports.type,
                data_imports.original_name, data_imports.comment, data_imports.created_at,
                concat(users.first_name, " ", users.last_name) as created_by'))
            ->join('users', 'data_imports.created_by', '=', 'users.id');
    }

    public function index()
    {
        $importTypes = $this->buildSelectList((new ImportTypes())->getData());

        return view('laravel-enso/data-import::dataImport.index', compact('importTypes'));
    }

    public function getSummary(DataImport $dataImport)
    {
        return $dataImport->summary;
    }

    public function run(string $type)
    {
        $importer = null;

        \DB::transaction(function () use (&$importer, $type) {
            $this->fileManager->startUpload($this->request->allFiles());
            $uploadedFile = $this->fileManager->getUploadedFiles()->first();
            $importer = new Importer($type, $uploadedFile);
            $importer->run();

            if ($importer->fails()) {
                $this->fileManager->deleteTempFiles();

                return $importer->getSummary();
            }

            $dataImport = new DataImport($uploadedFile);
            $dataImport->type = $type;
            //$dataImport->comment = $comment;
            $dataImport->summary = $importer->getSummary();
            $dataImport->save();
            $this->fileManager->commitUpload();
        });

        return $importer->getSummary();
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
