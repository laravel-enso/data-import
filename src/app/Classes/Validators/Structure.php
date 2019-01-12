<?php

namespace LaravelEnso\DataImport\app\Classes\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\DataImport\app\Classes\Summary;
use LaravelEnso\DataImport\app\Classes\Template;
use LaravelEnso\DataImport\app\Classes\Worksheet\Worksheet;

class Structure
{
    protected $template;
    protected $worksheet;
    protected $summary;

    public function __construct(Template $template, Worksheet $worksheet, Summary $summary)
    {
        $this->template = $template;
        $this->worksheet = $worksheet;
        $this->summary = $summary;
    }

    public function run()
    {
        $this->checkSheets();

        if (! $this->fails()) {
            $this->checkColumns();
        }

        return $this;
    }

    public function fails()
    {
        return $this->summary->hasErrors();
    }

    public function summary()
    {
        return $this->summary;
    }

    private function checkSheets()
    {
        $templateSheets = $this->template->sheetNames();
        $fileSheets = $this->worksheet->sheetNames();

        $this->missingSheets($templateSheets, $fileSheets)
            ->extraSheets($templateSheets, $fileSheets);
    }

    private function missingSheets(Collection $templateSheets, Collection $fileSheets)
    {
        $templateSheets->diff($fileSheets)
            ->each(function ($sheetName) {
                $this->summary->addError(__('Missing Sheets'), $sheetName);
            });

        return $this;
    }

    private function extraSheets(Collection $templateSheets, Collection $fileSheets)
    {
        $fileSheets->diff($templateSheets)
            ->each(function ($sheetName) {
                $this->summary->addError(__('Extra Sheets'), $sheetName);
            });
    }

    private function checkColumns()
    {
        $this->worksheet->sheets()->each(function ($sheet) {
            $templateHeader = $this->template->header($sheet->name());

            $this->missingColumns(
                    $sheet->name(), $sheet->header(), $templateHeader
                )->extraColumns(
                    $sheet->name(), $sheet->header(), $templateHeader
                );
        });
    }

    private function missingColumns(string $sheetName, Collection $fileHeader, Collection $templateHeader)
    {
        $templateHeader->diff($fileHeader)
            ->each(function ($column) use ($sheetName) {
                $this->summary->addError(
                    __('Missing Columns'), __(
                        'Sheet ":sheet", column ":column"',
                        ['sheet' => $sheetName, 'column' => $column]
                    )
                );
            });

        return $this;
    }

    private function extraColumns(string $sheetName, Collection $fileHeader, Collection $templateHeader)
    {
        $fileHeader->diff($templateHeader)
            ->each(function ($column) use ($sheetName) {
                $this->summary->addError(
                    __('Extra Columns'), __(
                        'Sheet ":sheet", column ":column"',
                        ['sheet' => $sheetName, 'column' => $column]
                    )
                );
            });
    }
}
