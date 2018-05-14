<?php

namespace LaravelEnso\DataImport\app\Classes\Handlers;

use LaravelEnso\DataImport\app\Models\ImportTemplate;

class Storer extends Handler
{
    private $file;
    private $type;
    private $template;

    public function __construct(array $file, string $type)
    {
        parent::__construct();

        $this->file = $file;
        $this->type = $type;

        $this->fileManager->tempPath(config('enso.config.paths.temp'));
    }

    public function run()
    {
        $this->upload();

        return $this->template;
    }

    private function upload()
    {
        try {
            \DB::transaction(function () {
                $this->fileManager->startUpload($this->file);
                $this->template = $this->store();
                $this->fileManager->commitUpload();
            });
        } catch (\Exception $exception) {
            $this->fileManager->deleteTempFiles();
            throw $exception;
        }
    }

    private function store()
    {
        return ImportTemplate::create(
            $this->fileManager->uploadedFiles()->first() +
            ['type' => $this->type]
        );
    }
}
