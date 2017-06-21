<?php

namespace LaravelEnso\DataImport\app\Classes\Validators;

use LaravelEnso\DataImport\app\Classes\Reporting\ValidationSummary;
use LaravelEnso\Helpers\Classes\AbstractObject;
use Maatwebsite\Excel\Collections\SheetCollection;

abstract class AbstractValidator extends AbstractObject
{
    protected $template;
    protected $xlsx;
    protected $summary;

    public function __construct($template, SheetCollection $xlsx, ValidationSummary $summary)
    {
        $this->template = $template;
        $this->xlsx = $xlsx;
        $this->summary = $summary;
    }

    abstract public function run();

    public function isValid()
    {
        return !$this->summary->hasErrors;
    }

    public function getSummary()
    {
        return $this->summary;
    }
}
