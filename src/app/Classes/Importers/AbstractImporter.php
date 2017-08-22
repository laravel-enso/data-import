<?php

namespace LaravelEnso\DataImport\app\Classes\Importers;

use LaravelEnso\DataImport\app\Classes\Reporting\ImportSummary;
use LaravelEnso\DataImport\app\Classes\Reporting\Issue;
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
        $sheet = $this->sheets->filter(function ($sheet) use ($sheetName) {
            return $sheet->getTitle() === $sheetName;
        })->first();

        if ($this->summary->hasContentErrors()) {
            $rows = $this->summary->getRowsWithIssues($sheetName);

            return $sheet->filter(function ($row, $index) use ($rows) {
                return !$rows->contains($index + 2);
            });
        }

        return $sheet;
    }

    public function addIssue(Issue $issue, string $category, string $sheetName = '')
    {
        $this->summary->addContentIssue($issue, $category, $sheetName);
    }

    public function incSuccess()
    {
        $this->summary->incSuccess();
    }
}
