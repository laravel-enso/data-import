<?php

namespace App\Imports\Validators;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\DataImport\app\Classes\Validators\Validator;

class CustomValidator extends Validator
{
    public function run(Obj $row)
    {
        //do custom validation logic
        //for registering errors use $this->addError(string $error) as many times as you need
    }
}
