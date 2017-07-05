<?php

namespace App\Importing\Validators;

use LaravelEnso\DataImport\app\Classes\Validators\AbstractValidator;

class CustomValidator extends AbstractValidator
{
    protected $template;
    protected $xlsx;
    protected $summary;

    public function run()
    {
        //do custom validation logic
        //for registering issues use the method below
        //addIssue($issue) where $issue is an array of the form
        //['category' => 'category', 'rowNumber' => 'rowNumber', 'column' => 'column', 'value' => 'value']
        //only 'category' is mandatory
    }
}
