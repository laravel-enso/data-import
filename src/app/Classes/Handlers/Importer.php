<?php

namespace LaravelEnso\DataImport\app\Classes\Handlers;

use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Classes\Importers\DataImporter;
use LaravelEnso\DataImport\app\Classes\Validators\Template as TemplateValidator;

class Importer extends Handler
{
    private const ValidExtensions = ['xls', 'xlsx'];

    private $file;
    private $type;

    public function __construct(array $file, string $type)
    {
        parent::__construct();

        $this->file = $file;
        $this->type = $type;
    }

    public function run()
    {
        return $this->validateTemplate()
            ->startUpload()
            ->import();
    }

    private function import()
    {
        $importer = $this->importerInstance();

        \DB::transaction(function () use ($importer) {
            $importer->run();

            if ($importer->fails()) {
                $this->fileManager->deleteTempFiles();

                return;
            }

            $this->storeModel($importer->summary());
            $this->fileManager->commitUpload();
        });

        return $importer->summary();
    }

    private function storeModel($summary)
    {
        DataImport::create(
            $this->fileManager->uploadedFiles()->first() + [
                'type' => $this->type,
                'summary' => $summary,
            ]
        );
    }

    private function validateTemplate()
    {
        if (config('app.env') === 'local') {
            (new TemplateValidator($this->type))->run();
        }

        return $this;
    }

    private function startUpload()
    {
        $this->fileManager->tempPath((config('enso.config.paths.temp')))
            ->validExtensions(self::ValidExtensions);

        $this->fileManager->startUpload($this->file);

        return $this;
    }

    private function importerInstance()
    {
        $file = $this->fileManager->uploadedFiles()->first();

        return new DataImporter($file, $this->type);
    }
}
