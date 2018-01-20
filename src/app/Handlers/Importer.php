<?php

namespace LaravelEnso\DataImport\app\Handlers;

use LaravelEnso\DataImport\app\Classes\XlsReader;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Classes\ImportConfiguration;
use LaravelEnso\DataImport\app\Classes\Importers\DataImporter;

class Importer extends Handler
{
    private $file;
    private $type;
    private $importer;

    public function __construct(array $file, string $type)
    {
        parent::__construct();

        $this->file = $file;
        $this->type = $type;
    }

    public function run()
    {
        $this->setUploader();

        $this->fileManager->startUpload($this->file);

        $this->setImporter();

        $this->import();

        return $this->importer->getSummary();
    }

    private function import()
    {
        \DB::transaction(function () {
            $this->importer->run();

            if ($this->importer->fails() || $this->importer->getSummary()->getSuccessfulCount() === 0) {
                $this->fileManager->deleteTempFiles();

                return;
            }

            $this->storeModel();
            $this->fileManager->commitUpload();
        });
    }

    private function setImporter()
    {
        $file = $this->fileManager->uploadedFiles()->first();
        $sheets = (new XlsReader($file['full_path']))->get();
        $config = new ImportConfiguration($this->type);

        $this->importer = new DataImporter($file['original_name'], $config, $sheets);
    }

    private function storeModel()
    {
        DataImport::create(
            $this->fileManager->uploadedFiles()->first() + [
                'type' => $this->type,
                'summary' => $this->importer->getSummary(),
            ]
        );
    }

    private function setUploader()
    {
        $this->fileManager->tempPath((config('enso.config.paths.temp')))
            ->validExtensions(['xls', 'xlsx']);
    }
}
