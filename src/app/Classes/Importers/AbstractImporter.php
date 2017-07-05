<?php

namespace LaravelEnso\DataImport\app\Classes\Importers;

use LaravelEnso\DataImport\app\Classes\Reporting\ImportSummary;
use Maatwebsite\Excel\Collections\SheetCollection;

abstract class AbstractImporter
{
    protected $sheets;
    protected $summary;

    public function __construct(SheetCollection $sheets, ImportSummary $summary)
    {
        $this->sheets = $sheets;
        $this->summary = $summary;
    }

    abstract public function run();

    public function getSheet(string $sheetName)
    {
        return $this->sheets->filter(function ($sheet) use ($sheetName) {
            return $sheet->getTitle() === $sheetName;
        })->first();
    }

    public function incSuccess()
    {
        $this->summary->incSuccess();
    }
}
