<?php

namespace LaravelEnso\DataImport\app\Classes\Validators;

use LaravelEnso\DataImport\app\Classes\Reporting\ImportSummary;
use LaravelEnso\DataImport\app\Classes\Template;
use LaravelEnso\Helpers\Classes\AbstractObject;
use Maatwebsite\Excel\Collections\SheetCollection;

abstract class AbstractValidator
{
    protected $template;
    protected $sheets;
    protected $summary;

    public function __construct(Template $template, SheetCollection $sheets, ImportSummary $summary)
    {
        $this->template = $template;
        $this->sheets = $sheets;
        $this->summary = $summary;
    }

    abstract public function run();

    public function isValid()
    {
        return !$this->summary->hasErrors();
    }

    public function fails()
    {
        return $this->summary->hasErrors();
    }

    public function getSummary()
    {
        return $this->summary;
    }
}
