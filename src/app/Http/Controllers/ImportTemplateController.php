<?php

namespace LaravelEnso\DataImport\App\Http\Controllers;

use App\Http\Controllers\Controller;
use LaravelEnso\DataImport\app\Models\ImportTemplate;
use LaravelEnso\FileManager\Classes\FileManager;

class ImportTemplateController extends Controller
{
    private $fileManager;

    public function __construct()
    {
        $this->fileManager = new FileManager(config('laravel-enso.paths.imports'), config('laravel-enso.paths.temp'));
    }

    public function getTemplate($type)
    {
        $template = ImportTemplate::whereType($type)->first();

        return $template ?: new ImportTemplate();
    }

    public function upload()
    {
        $this->checkIfFileIsValid();
        $template = null;

        \DB::transaction(function () use (&$template) {
            $this->fileManager->startSingleFileUpload(request('file_0'));
            $template = new ImportTemplate($this->fileManager->uploadedFiles->first());
            $template->type = request('type');
            $template->save();
            $this->fileManager->commitUpload();
        });

        return $template;
    }

    private function checkIfFileIsValid()
    {
        if (!request('file_0')->isValid()) {
            throw new \EnsoException('The file is not valid', 'error', 400);
        }
    }

    public function download(ImportTemplate $template)
    {
        $fileWrapper = $this->fileManager->getFile($template->saved_name);
        $fileWrapper->originalName = $template->original_name;

        return $fileWrapper->getDownloadResponse();
    }

    public function destroy(ImportTemplate $template)
    {
        \DB::transaction(function () use ($template) {
            $this->fileManager->delete($template->saved_name);
            $template->delete();
        });

        return $this->fileManager->getStatus();
    }
}
