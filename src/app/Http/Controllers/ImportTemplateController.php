<?php

namespace LaravelEnso\DataImport\App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use LaravelEnso\DataImport\app\Models\ImportTemplate;
use LaravelEnso\FileManager\Classes\FileManager;

class ImportTemplateController extends Controller
{

    /**
     * @var FileManager
     */
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

    public function upload(Request $request, $type)
    {
        $this->checkIfFileIsValid();
        $template = null;

        \DB::transaction(function () use (&$template, $request, $type) {
            $this->fileManager->startUpload($request->all());
            $template = new ImportTemplate($this->fileManager->getUploadedFiles()->first());
            $template->type = $type;
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
        return $this->fileManager->download($template->original_name, $template->saved_name);
    }

    public function destroy(ImportTemplate $template)
    {
        \DB::transaction(function () use ($template) {
            $this->fileManager->delete($template->saved_name);
            $template->delete();
        });

        return response(__('Deleted'));
    }
}
