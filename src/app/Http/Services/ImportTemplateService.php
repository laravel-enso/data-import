<?php

namespace LaravelEnso\DataImport\App\Http\Services;

use Illuminate\Http\Request;
use LaravelEnso\FileManager\app\Classes\FileManager;
use LaravelEnso\DataImport\app\Models\ImportTemplate;

class ImportTemplateService
{
    private $fileManager;

    public function __construct()
    {
        $this->fileManager = new FileManager(config('enso.config.paths.imports'));
    }

    public function getTemplate(string $type)
    {
        $template = ImportTemplate::whereType($type)->first();

        return $template ?: new ImportTemplate();
    }

    public function store(Request $request, string $type)
    {
        $this->setUploader();

        $template = null;

        \DB::transaction(function () use (&$template, $request, $type) {
            $this->fileManager->startUpload($request->allFiles());
            $template = new ImportTemplate($this->fileManager->uploadedFiles()->first());
            $template->type = $type;
            $template->save();
            $this->fileManager->commitUpload();
        });

        return $template;
    }

    public function show(ImportTemplate $template)
    {
        return $this->fileManager->download($template->original_name, $template->saved_name);
    }

    public function destroy(ImportTemplate $template)
    {
        \DB::transaction(function () use ($template) {
            $template->delete();
            $this->fileManager->delete($template->saved_name);
        });

        return ['message' => __(config('enso.labels.successfulOperation'))];
    }

    private function setUploader()
    {
        $this->fileManager->tempPath(config('enso.config.paths.temp'))
            ->validExtensions(['xls', 'xlsx']);
    }
}
