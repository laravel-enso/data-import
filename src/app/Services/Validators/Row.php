<?php

namespace LaravelEnso\DataImport\app\Services\Validators;

use LaravelEnso\Helpers\app\Classes\Obj;

class Row extends Validator
{
    private $rules;
    private $validator;

    public function run(Obj $row)
    {
        $this->validator = validator($row->all(), $this->rules);

        if ($this->validator->fails()) {
            $this->addErrors();
        }
    }

    public function rules(array $rules)
    {
        $this->rules = $rules;

        return $this;
    }

    private function addErrors()
    {
        collect($this->rules)->keys()
            ->filter(fn($column) => $this->validator->errors()->has($column))
            ->each(fn($column) => (
                collect($this->validator->errors()->get($column))
                    ->each(fn($error) => $this->addError($error))
            ));
    }
}
