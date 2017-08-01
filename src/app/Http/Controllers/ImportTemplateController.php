<?php

namespace LaravelEnso\DataImport\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use LaravelEnso\DataImport\App\Http\Services\ImportTemplateService;
use LaravelEnso\DataImport\app\Models\ImportTemplate;

class ImportTemplateController extends Controller
{
    private $importTemplates;

    public function __construct(Request $request)
    {
        $this->importTemplates = new ImportTemplateService($request);
    }

    public function getTemplate(string $type)
    {
        return $this->importTemplates->getTemplate($type);
    }

    public function store(string $type)
    {
        return $this->importTemplates->store($type);
    }

    public function show(ImportTemplate $template)
    {
        return $this->importTemplates->show($template);
    }

    public function destroy(ImportTemplate $template)
    {
        return $this->importTemplates->destroy($template);
    }
}
