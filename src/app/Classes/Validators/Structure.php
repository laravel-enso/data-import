<?php

namespace LaravelEnso\DataImport\app\Classes\Validators;

use Illuminate\Support\Collection;

class Structure extends Validator
{
    public function run()
    {
        $this->checkSheets();

        if (!$this->fails()) {
            $this->checkColumns();

            if (!$this->fails()) {
                $this->checkEntryLimit();
            }
        }

        return $this;
    }

    public function fails()
    {
        return $this->summary->hasIssues();
    }

    private function checkSheets()
    {
        $templateSheets = $this->template->sheetNames();
        $xlsxSheets = $this->xlsxSheetNames();
        $this->setExtraSheets($templateSheets, $xlsxSheets);
        $this->setMissingSheets($templateSheets, $xlsxSheets);
    }

    private function setExtraSheets(Collection $templateSheets, Collection $xlsxSheets)
    {
        $extraSheets = $xlsxSheets->diff($templateSheets);

        $extraSheets->each(function ($sheet) {
            $this->addIssue(__('Extra Sheets'), $sheet);
        });
    }

    private function setMissingSheets(Collection $templateSheets, Collection $xlsxSheets)
    {
        $missingSheets = $templateSheets->diff($xlsxSheets);

        $missingSheets->each(function ($sheet) {
            $this->addIssue(__('Missing Sheets'), $sheet);
        });
    }

    private function checkColumns()
    {
        $this->sheets->each(function ($sheet) {
            if ($sheet->rows()->count()) {
                $xlsxColumns = collect($sheet->rows()->first()->keys());
                $templateColumns = $this->template->columns($sheet->name());
                $this->setMissingColumns($sheet->name(), $xlsxColumns, $templateColumns);
                $this->setExtraColumns($sheet->name(), $xlsxColumns, $templateColumns);
            }
        });
    }

    private function setMissingColumns(string $sheetName, Collection $xlsxColumns, Collection $templateColumns)
    {
        $templateColumns->diff($xlsxColumns)
            ->each(function ($column) use ($sheetName) {
                $message = __(
                    'Sheet ":sheet", column ":column"',
                    ['sheet' => $sheetName, 'column' => $column]
                );

                $this->addIssue(__('Missing Columns'), $message);
            });
    }

    private function setExtraColumns(string $sheetName, Collection $xlsxColumns, Collection $templateColumns)
    {
        $xlsxColumns->diff($templateColumns)
            ->each(function ($column) use ($sheetName) {
                $message = __(
                    'Sheet ":sheet", column ":column"',
                    ['sheet' => $sheetName, 'column' => $column]
                );

                $this->addIssue(__('Extra Columns'), $message);
            });
    }

    private function checkEntryLimit()
    {
        $this->sheets->each(function ($sheet) {
            if ($sheet->rows()->count() > $this->template->entryLimit()) {
                $category = __(
                    'Exceeded the entries limit of: :limit',
                    ['limit' => $this->template->entryLimit()]
                );

                $message = __(
                    'Sheet ":sheet", count :count',
                    ['sheet' => $sheet->name(), 'count' => $sheet->rows()->count()]
                );

                $this->addIssue($category, $message);
            }
        });
    }

    private function xlsxSheetNames()
    {
        return $this->sheets
            ->map(function ($sheet) {
                return $sheet->name();
            });
    }

    private function addIssue(string $category, string $value)
    {
        $this->summary->addStructureIssue(...func_get_args());
    }
}
