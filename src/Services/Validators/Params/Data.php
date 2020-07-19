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
    protected Obj $errors;
    protected Validator $validator;

    public function __construct(Template $template, $params)
    {
        $this->template = $template;
        $this->params = $params;
        $this->errors = new Obj();

    }

    public function validate()
    {
        Facade::make(
            $this->params->all(),
            $this->template->paramRules()
        )->validate();
    }
}
