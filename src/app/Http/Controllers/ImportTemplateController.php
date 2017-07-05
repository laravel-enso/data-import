<?php

namespace LaravelEnso\DataImport\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use LaravelEnso\DataImport\App\Http\Services\ImportTemplateService;
use LaravelEnso\DataImport\app\Models\ImportTemplate;

class ImportTemplateController extends Controller
{
    private $templateService;

    public function __construct(Request $request)
    {
        $this->templateService = new ImportTemplateService($request);
    }

    public function getTemplate(string $type)
    {
        return $this->templateService->getTemplate($type);
    }

    public function upload(string $type)
    {
        return $this->templateService->upload($type);
    }

    public function download(ImportTemplate $template)
    {
        return $this->templateService->download($template);
    }

    public function destroy(ImportTemplate $template)
    {
        return $this->templateService->destroy($template);
    }
}
