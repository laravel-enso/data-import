<?php
/**
 * Created by PhpStorm.
 * User: mihai
 * Date: 03.04.2017
 * Time: 15:20
 */

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
        //for registering issues use the method below, with the required parameters
        //addContentIssue(string $sheetName, string $category, int $rowNumber, string $column, $value)
    }
}
