<?php

namespace LaravelEnso\DataImport\App\Http\Services;

use Illuminate\Http\Request;
use LaravelEnso\DataImport\app\Models\ImportTemplate;
use LaravelEnso\FileManager\Classes\FileManager;

class ImportTemplateService
{
    private $request;
    private $fileManager;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->fileManager = new FileManager(
            config('laravel-enso.paths.imports'),
            config('laravel-enso.paths.temp')
        );

        $this->fileManager->setValidExtensions(['xls', 'xlsx']);
    }

    public function getTemplate(string $type)
    {
        $template = ImportTemplate::whereType($type)->first();

        return $template ?: new ImportTemplate();
    }

    public function store(string $type)
    {
        $template = null;

        \DB::transaction(function () use (&$template, $type) {
            $this->fileManager->startUpload($this->request->allFiles());
            $template = new ImportTemplate($this->fileManager->getUploadedFiles()->first());
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
}
