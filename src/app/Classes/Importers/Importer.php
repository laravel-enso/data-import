<?php

namespace LaravelEnso\DataImport\app\Classes\Importers;

use Illuminate\Support\Collection;
use LaravelEnso\DataImport\app\Classes\Summary;

abstract class Importer
{
    protected $sheets;
    protected $summary;

    public function __construct(Collection $sheets, Summary $summary)
    {
        $this->sheets = $sheets;
        $this->summary = $summary;
    }

    abstract public function run();

    protected function rowsFromSheet(string $sheetName)
    {
        $sheet = $this->sheets
            ->first(function ($sheet) use ($sheetName) {
                return $sheet->name() === $sheetName;
            });

        if ($this->summary->hasContentIssues()) {
            $rows = $this->summary->rowsWithIssues($sheetName);

            return $sheet->rows()
                ->filter(function ($row, $index) use ($rows) {
                    return ! $rows->contains($index + 2);
                });
        }

        return $sheet->rows();
    }

    public function incSuccess()
    {
        $this->summary->incSuccess();
    }
}
