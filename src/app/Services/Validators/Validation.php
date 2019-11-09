<?php

namespace LaravelEnso\DataImport\app\Services\Validators;

use LaravelEnso\DataImport\app\Services\Validators\Row as ImplicitValidator;
use LaravelEnso\Helpers\app\Classes\Obj;

class Validation
{
    private $row;
    private $rules;
    private $custom;
    private $params;
    private $errorColumn;

    public function __construct(Obj $row, array $rules, ?Validator $custom, ?Obj $params)
    {
        $this->row = $row;
        $this->rules = $rules;
        $this->custom = $custom;
        $this->params = $params;
        $this->errorColumn = config('enso.imports.errorColumn');
    }

    public function run()
    {
        $this->runValidator($this->implicit())
            ->runValidator($this->custom);
    }

    private function runValidator($validator)
    {
        if (! $validator) {
            return $this;
        }

        $validator->setParams($this->params)->run($this->row);

        if ($validator->fails()) {
            $this->addErrors($validator->message());
        }

        return $this;
    }

    private function implicit()
    {
        return (new ImplicitValidator())->rules($this->rules);
    }

    private function addErrors($errors)
    {
        $this->row->set(
            $this->errorColumn,
            $this->row->has($this->errorColumn)
                ? $this->row->get($this->errorColumn).' | '.$errors
                : $errors
        );
    }
}
