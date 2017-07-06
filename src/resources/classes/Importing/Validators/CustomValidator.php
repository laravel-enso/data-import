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
        //for registering issues in the summary use the method below
        //$this->summary->addIssue($issue) where $issue is an object of the
        //'LaravelEnso\DataImport\app\Classes\Reporting\Issue' class that can be instantiated with
        //an array with the following parameters
        //['category' => 'category', 'rowNumber' => 'rowNumber', 'column' => 'column', 'value' => 'value']
        //Note: only 'category' parameter is mandatory
    }
}