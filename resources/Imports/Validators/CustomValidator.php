<?php

namespace App\Imports\Validators;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\DataImport\app\Services\Validators\Validator;

class CustomValidator extends Validator
{
    public function run(Obj $row)
    {
        // do custom validation logic
        // $this->addError(string $error) to register errors as many times as you need
    }
}
