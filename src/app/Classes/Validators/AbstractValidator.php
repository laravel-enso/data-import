<?php

namespace LaravelEnso\DataImport\app\Classes\Validators;

use LaravelEnso\DataImport\app\Classes\Template;
use Maatwebsite\Excel\Collections\SheetCollection;
use LaravelEnso\DataImport\app\Classes\Reporting\ImportSummary;

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

    public function hasStructureErrors()
    {
        return $this->summary->hasStructureErrors();
    }

    public function hasContentErrors()
    {
        return $this->summary->hasContentErrors();
    }

    public function getSummary()
    {
        return $this->summary;
    }

    public function getSheet(string $sheetName)
    {
        return $this->sheets->filter(function ($sheet) use ($sheetName) {
            return $sheet->getTitle() === $sheetName;
        })->first();
    }
}
