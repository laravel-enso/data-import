<?php

namespace LaravelEnso\DataImport\App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use LaravelEnso\DataImport\app\Models\ImportTemplate;
use LaravelEnso\DataImport\App\Http\Services\ImportTemplateService;

class ImportTemplateController extends Controller
{
    public function getTemplate(string $type, ImportTemplateService $service)
    {
        return $service->getTemplate($type);
    }

    public function store(Request $request, string $type, ImportTemplateService $service)
    {
        return $service->store($request, $type);
    }

    public function show(ImportTemplate $template, ImportTemplateService $service)
    {
        return $service->show($template);
    }

    public function destroy(ImportTemplate $template, ImportTemplateService $service)
    {
        return $service->destroy($template);
    }
}
