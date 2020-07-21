<?php

namespace LaravelEnso\DataImport\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use LaravelEnso\DataImport\Models\ImportTemplate;
use LaravelEnso\DataImport\Services\Template;

class Import implements Responsable
{
    private Template $template;
    private string $type;

    public function __construct(string $type, Template $template)
    {
        $this->template = $template;
        $this->type = $type;
    }

    public function toResponse($request)
    {
        return [
            'import' => [
                'params' => $this->params(),
                'template' => $this->template(),
            ],
        ];
    }

    protected function params()
    {
        return $this->template->params()->map->except('validations');
    }

    protected function template()
    {
        return ImportTemplate::whereType($this->type)->first();
    }
}
