<?php

namespace LaravelEnso\DataImport\Services\Validators;

use Illuminate\Support\Facades\Validator as Facade;
use LaravelEnso\DataImport\Services\Template;
use LaravelEnso\Helpers\Services\Obj;
use Illuminate\Contracts\Validation\Validator;

class Params
{
    protected Template $template;
    protected Obj $params;
    protected Obj $errors;
    protected Validator $validator;

    public function __construct(Template $template, Obj $params)
    {
        $this->template = $template;
        $this->params = $params;
        $this->errors = new Obj();

        $this->run();
    }

    private function run()
    {
        $this->validator = Facade::make(
            $this->params->all(),
            $this->template->paramRules()
        );
    }

    public function isInvalid(): bool
    {
        return $this->validator->fails();
    }

    public function toArray(): array
    {
        return [
            'type' => 'params',
            'errors' => $this->validator->errors(),
        ];
    }
}
