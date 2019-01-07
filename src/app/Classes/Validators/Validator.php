<?php

namespace LaravelEnso\DataImport\app\Classes\Validators;

use LaravelEnso\Helpers\app\Classes\Obj;

abstract class Validator
{
    private $errors;

    public function __construct()
    {
        $this->errors = collect();
    }

    abstract public function run(Obj $row);

    public function fails()
    {
        return $this->errors->isNotEmpty();
    }

    public function message()
    {
        return $this->errors->implode(' | ');
    }

    public function addError(string $error)
    {
        $this->errors->push($error);
    }
}
