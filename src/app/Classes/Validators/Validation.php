<?php

namespace LaravelEnso\DataImport\app\Classes\Validators;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\DataImport\app\Classes\Validators\Row as BasicValidator;

class Validation
{
    private $row;
    private $rules;
    private $customValidator;
    private $errorColumn;
    private $flag = false;

    public function __construct(Obj $row, array $rules, ?Validator $customValidator)
    {
        $this->row = $row;
        $this->rules = $rules;
        $this->customValidator = $customValidator;
        $this->errorColumn = config('enso.imports.errorColumn');
    }

    public function run()
    {
        $this->runValidator($this->basicValidator())
            ->runValidator($this->customValidator);
    }

    private function runValidator($validator)
    {
        if (! $validator) {
            return $this;
        }

        $validator->run($this->row);

        if ($validator->fails()) {
            $this->row->set(
                $this->errorColumn,
                $this->row->has($this->errorColumn)
                    ? $this->row->get($this->errorColumn).' | '.$validator->message()
                    : $validator->message()
            );
        }

        return $this;
    }

    private function basicValidator()
    {
        return (new BasicValidator())
            ->rules($this->rules);
    }
}
