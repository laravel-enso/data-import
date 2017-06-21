<?php

namespace LaravelEnso\DataImport\app\Classes\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\DataImport\app\Classes\Reporting\ValidationSummary;
use Maatwebsite\Excel\Collections\SheetCollection;

class StructureValidator extends AbstractValidator
{
    protected $template;
    protected $xlsx;
    protected $summary;
    protected $sheetEntriesLimit;

    public function __construct($template, SheetCollection $xlsx, ValidationSummary $summary, int $sheetEntriesLimit)
    {
        parent::__construct($template, $xlsx, $summary);
        $this->sheetEntriesLimit = $sheetEntriesLimit;
    }

    public function run()
    {
        $this->validateSheets();

        if ($this->summary->hasErrors) {
            return;
        }

        $this->validateSheetLimit();

        $this->validateColumns();
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
            $this->summary->addStructureIssue(__('Extra Sheets'), $sheet);
        });
    }

    private function getMissingSheets(Collection $templateSheets, Collection $xlsxSheets)
    {
        $missingSheets = $templateSheets->diff($xlsxSheets);

        $missingSheets->each(function ($sheet) {
            $this->summary->addStructureIssue(__('Missing Sheets'), $sheet);
        });
    }

    private function validateColumns()
    {
        $this->xlsx->each(function ($sheet) {
            if (($sheet)->count()) {
                $header = $this->getHeaderFromRow($sheet);
                $templateColumns = $this->template->getColumnsFromSheet($sheet->getTitle());
                $this->getMissingColumnsFromSheet($sheet->getTitle(), $header, $templateColumns);
                $this->getExtraColumnsFromSheet($sheet->getTitle(), $header, $templateColumns);
            }
        });
    }

    private function getHeaderFromRow(Collection $row)
    {
        return collect(array_keys($row->first()->toArray()));
    }

    private function getMissingColumnsFromSheet(string $sheetName, Collection $header, Collection $templateColumns)
    {
        $missingColumns = $templateColumns->diff($header);

        $missingColumns->each(function ($column) use ($sheetName) {
            $this->summary->addStructureIssue(__('Missing Columns'), $column, $sheetName);
        });
    }

    private function getExtraColumnsFromSheet(string $sheetName, Collection $header, Collection $templateColumns)
    {
        $extraColumns = $header->diff($templateColumns);

        $extraColumns->each(function ($column) use ($sheetName) {
            $this->summary->addStructureIssue(__('Extra Columns'), $column, $sheetName);
        });
    }

    private function validateSheetLimit()
    {
        $this->xlsx->each(function ($sheet) {
            if ($sheet->count() > $this->sheetEntriesLimit) {
                $message = __('Exceded the entries limit of: ').$this->sheetEntriesLimit.'. ';
                $message .= __('Current count: ').$sheet->count();
                $this->summary->addStructureIssue($message, $sheet->getTitle());
            }
        });
    }

    private function getXlsxSheetNames()
    {
        $xlsxSheets = collect();

        $this->xlsx->each(function ($sheet) use (&$xlsxSheets) {
            $xlsxSheets->push($sheet->getTitle());
        });

        return $xlsxSheets;
    }
}
