<?php

namespace LaravelEnso\DataImport\Services\Validators\Params;

use Illuminate\Support\Facades\Validator as Facade;
use LaravelEnso\DataImport\Services\Template;
use LaravelEnso\Helpers\Services\Obj;
use Illuminate\Contracts\Validation\Validator;

class Data
{
    protected Template $template;
    protected Obj $params;

    public function __construct(Template $template, $params)
    {
        $this->template = $template;
        $this->params = $params;

    }

    public function validate()
    {
        Facade::make(
            $this->params->all(),
            $this->template->paramRules()
        )->validate();
    }
}
