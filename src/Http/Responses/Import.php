<?php

namespace LaravelEnso\DataImport\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use LaravelEnso\DataImport\Services\Template;

class Import implements Responsable
{
    private Template $template;

    public function __construct(Template $template)
    {
        $this->template = $template;
    }

    public function toResponse($request)
    {
        return [
            'import' => [
                'params' => $this->params(),
            ],
        ];
    }

    protected function params()
    {
        return $this->template->params()->map->except('validations');
    }
}
