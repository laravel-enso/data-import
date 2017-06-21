<?php

namespace LaravelEnso\DataImport\app\Classes;

use LaravelEnso\DataImport\app\Classes\Reporting\ValidationSummary;
use Maatwebsite\Excel\Collections\SheetCollection;

abstract class AbstractImporter
{
    protected $xlsx;
    protected $summary;

    public function __construct(SheetCollection $xlsx, ValidationSummary $summary)
    {
        $this->xlsx = $xlsx;
        $this->summary = $summary;
    }

    abstract public function run();

    protected function getSheet(string $sheetName)
    {
        return $this->xlsx->filter(function ($sheet) use ($sheetName) {
            return $sheet->getTitle() === $sheetName;
        })->first();
    }

    protected function incSuccess()
    {
        $this->summary->incSuccess();
    }
}
