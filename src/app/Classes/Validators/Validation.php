<?php

namespace LaravelEnso\DataImport\app\Classes\Validators;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\DataImport\app\Classes\Validators\Row as ImplicitValidator;

class Validation
{
    private $row;
    private $rules;
    private $custom;
    private $errorColumn;

    public function __construct(Obj $row, array $rules, ?Validator $custom)
    {
        $this->row = $row;
        $this->rules = $rules;
        $this->custom = $custom;
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

        $validator->run($this->row);

        if ($validator->fails()) {
            $this->addErrors($validator->message());
        }

        return $this;
    }

    private function implicit()
    {
        return (new ImplicitValidator())
            ->rules($this->rules);
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
