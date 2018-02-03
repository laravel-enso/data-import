<?php

namespace App\Imports\Validators;

use LaravelEnso\DataImport\app\Classes\Validators\Validator;

class CustomValidator extends Validator
{
    public function run()
    {
        //do supplementary content validation logic
        //for registering issues in the summary use the method below
        //$this->summary->addContentIssue(string $sheetName, string $category, int $rowNumber = null, string $column = null, $value = null)
        //Note: be sure to pass $currentIndex + 2 as a parameter for 'rowNumber' to have it reported user friendly ;)
    }
}
