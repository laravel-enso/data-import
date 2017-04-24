<?php
/**
 * Created by PhpStorm.
 * User: mihai
 * Date: 22.02.2017
 * Time: 16:05
 */

namespace LaravelEnso\DataImport\app\Classes\Validators;

use LaravelEnso\DataImport\app\Classes\Reporting\ValidationSummary;
use LaravelEnso\Helpers\Classes\Object;
use Maatwebsite\Excel\Collections\SheetCollection;

abstract class AbstractValidator extends Object
{
    protected $template;
    protected $xlsx;
    protected $summary;

    public function __construct($template, SheetCollection $xlsx, ValidationSummary $summary)
    {
        $this->template = $template;
        $this->xlsx     = $xlsx;
        $this->summary  = $summary;
    }

    abstract public function run();

    public function isValid(): bool
    {
        return !$this->summary->hasErrors;
    }

    public function getSummary(): ValidationSummary
    {
        return $this->summary;
    }
}
