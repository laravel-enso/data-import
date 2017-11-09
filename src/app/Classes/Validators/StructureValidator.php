<?php

namespace LaravelEnso\DataImport\app\Classes\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\DataImport\app\Classes\ImportConfiguration;
use LaravelEnso\DataImport\app\Classes\Reporting\ImportSummary;
use LaravelEnso\DataImport\app\Classes\Reporting\Issue;
use Maatwebsite\Excel\Collections\SheetCollection;

class StructureValidator extends AbstractValidator
{
    protected $sheetEntriesLimit;

    public function __construct(ImportConfiguration $config, SheetCollection $sheets, ImportSummary $summary)
    {
        parent::__construct($config->getTemplate(), $sheets, $summary);

        $this->sheetEntriesLimit = $config->getSheetEntriesLimit();
    }

    public function run()
    {
        $this->validateSheets();

        if (!$this->summary->hasErrors()) {
            $this->validateColumns();
        }

        if (!$this->summary->hasErrors()) {
            $this->validateSheetEntriesLimit();
        }
    }

    private function validateSheets()
    {
        $templateSheets = $this->template->getSheetNames();
        $xlsxSheets = $this->getXlsxSheetNames();
        $this->getExtraSheets($templateSheets, $xlsxSheets);
        $this->getMissingSheets($templateSheets, $xlsxSheets);
    }

    private function getExtraSheets(Collection $templateSheets, Collection $xlsxSheets)
    {
        $extraSheets = $xlsxSheets->diff($templateSheets);

        $extraSheets->each(function ($sheet) {
            $this->addIssue(__(config('enso.importing.validationLabels.extra_sheets')), $sheet);
        });
    }

    private function getMissingSheets(Collection $templateSheets, Collection $xlsxSheets)
    {
        $missingSheets = $templateSheets->diff($xlsxSheets);

        $missingSheets->each(function ($sheet) {
            $this->addIssue(__(config('enso.importing.validationLabels.missing_sheets')), $sheet);
        });
    }

    private function validateColumns()
    {
        $this->sheets->each(function ($sheet) {
            if ($sheet->count()) {
                $xlsxSheetColumns = $sheet->first()->keys();
                $templateSheetColumns = $this->template->getColumnsFromSheet($sheet->getTitle());
                $this->getMissingColumns($sheet->getTitle(), $xlsxSheetColumns, $templateSheetColumns);
                $this->getExtraColumns($sheet->getTitle(), $xlsxSheetColumns, $templateSheetColumns);
            }
        });
    }

    private function getMissingColumns(string $sheetName, Collection $xlsxSheetColumns, Collection $templateSheetColumns)
    {
        $missingColumns = $templateSheetColumns->diff($xlsxSheetColumns);

        $missingColumns->each(function ($column) use ($sheetName) {
            $this->addIssue(__(config('enso.importing.validationLabels.missing_columns')), $column, $sheetName);
        });
    }

    private function getExtraColumns(string $sheetName, Collection $xlsxSheetColumns, Collection $templateSheetColumns)
    {
        $extraColumns = $xlsxSheetColumns->diff($templateSheetColumns);

        $extraColumns->each(function ($column) use ($sheetName) {
            $this->addIssue(__(config('enso.importing.validationLabels.extra_columns')), $column, $sheetName);
        });
    }

    private function validateSheetEntriesLimit()
    {
        $this->sheets->each(function ($sheet) {
            if ($sheet->count() > $this->sheetEntriesLimit) {
                $category = config('enso.importing.validationLabels.sheet_entries_limit_exceeded').': '.$this->sheetEntriesLimit;
                $this->addIssue($category, $sheet->count(), $sheet->getTitle());
            }
        });
    }

    private function getXlsxSheetNames()
    {
        $xlsxSheets = collect();

        $this->sheets->each(function ($sheet) use (&$xlsxSheets) {
            $xlsxSheets->push($sheet->getTitle());
        });

        return $xlsxSheets;
    }

    private function addIssue(string $category, string $value)
    {
        $issue = new Issue([
            'value' => $value,
        ]);

        $this->summary->addStructureIssue($issue, $category);
    }
}
