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
        foreach (array_keys($this->rules) as $column) {
            if ($this->validator->errors()->has($column)) {
                foreach ($this->validator->errors()->get($column) as $error) {
                    $this->addError($error);
                }
            }
        }
    }

    public function rules(array $rules)
    {
        $this->rules = $rules;

        return $this;
    }
}
