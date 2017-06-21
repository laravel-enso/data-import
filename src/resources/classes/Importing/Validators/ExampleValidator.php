<?php

namespace App\Importing\Validators;

use LaravelEnso\DataImport\app\Classes\Validators\AbstractValidator;

class ExampleValidator extends AbstractValidator
{
    protected $template;
    protected $xlsx;
    protected $summary;

    public function run()
    {
        //do custom validation logic
        //for registering issues use :
        //addContentIssue(string $sheetName, string $category, int $rowNumber, string $column, $value)
    }
}
