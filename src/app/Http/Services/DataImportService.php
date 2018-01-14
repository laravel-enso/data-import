<?php

namespace LaravelEnso\DataImport\App\Http\Services;

use Illuminate\Http\Request;
use LaravelEnso\DataImport\app\Classes\XlsReader;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\FileManager\app\Classes\FileManager;
use LaravelEnso\DataImport\app\Classes\Importers\Importer;
use LaravelEnso\DataImport\app\Classes\ImportConfiguration;

class DataImportService
{
    private $fileManager;

    public function __construct()
    {
        $this->fileManager = new FileManager(config('enso.config.paths.imports'));
    }

    public function store(Request $request, string $type) //fixme. We need a class to handle the file reading process
    {
        $this->setUploader();
        $this->fileManager->startUpload($request->allFiles());
        $importer = $this->getImporter($type);

        \DB::transaction(function () use ($importer, $type) {
            $importer->run();

            if ($importer->fails() || $importer->getSummary()->getSuccessfulCount() === 0) {
                $this->fileManager->deleteTempFiles();

                return $importer->getSummary();
            }

            $this->createDataImportRecord($type, $importer->getSummary());
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

        return ['message' => __(config('enso.labels.successfulOperation'))];
    }

    private function getImporter(string $type)
    {
        $file = $this->fileManager->uploadedFiles()->first();
        $sheets = (new XlsReader($file['full_path']))->get();
        $config = new ImportConfiguration($type);

        return new Importer($file['original_name'], $config, $sheets);
    }

    private function createDataImportRecord($type, $summary)
    {
        $dataImport = new DataImport($this->fileManager->uploadedFiles()->first());
        $dataImport->type = $type;
        $dataImport->summary = $summary;
        $dataImport->save();
    }

    private function setUploader()
    {
        $this->fileManager->tempPath((config('enso.config.paths.temp')))
            ->validExtensions(['xls', 'xlsx']);
    }
}
