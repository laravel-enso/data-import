<?php

namespace LaravelEnso\DataImport\app\Classes\Validators;

class Row extends Validator
{
    private $rules;
    private $validator;
    private $errors;

    public function run($row)
    {
        $this->validator = validator($row->all(), $this->rules);

        if ($this->validator->fails()) {
            $this->addErrors();
        }
    }

    private function addErrors()
    {
        collect($this->rules)->keys()->each(function ($column) {
            if ($this->validator->errors()->has($column)) {
                collect($this->validator->errors()->get($column))
                    ->each(function ($error) {
                        $this->addError($error);
                    });
            }
        });
    }

    public function rules(array $rules)
    {
        $this->rules = $rules;

        return $this;
    }
}
