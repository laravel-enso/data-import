<?php

namespace LaravelEnso\DataImport\Services\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\DataImport\Services\DTOs\Sheets;
use LaravelEnso\DataImport\Services\Summary;
use LaravelEnso\DataImport\Services\Template;

class Structure
{
    protected Template $template;
    protected Sheets $sheets;
    protected Summary $summary;

    public function __construct(Template $template, Sheets $sheets, Summary $summary)
    {
        $this->template = $template;
        $this->sheets = $sheets;
        $this->summary = $summary;
    }

    public function run(): void
    {
        $this->sheets();

        if ($this->summary->errors()->isEmpty()) {
            $this->columns();
        }
    }

    public function summary(): Summary
    {
        return $this->summary;
    }

    private function sheets(): void
    {
        $templateSheets = $this->template->sheetNames();
        $fileSheets = $this->sheets->names();

        $this->missingSheets($templateSheets, $fileSheets)
            ->extraSheets($templateSheets, $fileSheets);
    }

    private function missingSheets(Collection $templateSheets, Collection $fileSheets): self
    {
        $templateSheets->diff($fileSheets)
            ->each(fn ($sheetName) => $this->summary
                ->addError(__('Missing Sheets'), $sheetName));

        return $this;
    }

    private function extraSheets(Collection $templateSheets, Collection $fileSheets): void
    {
        $fileSheets->diff($templateSheets)
            ->each(fn ($sheetName) => $this->summary
                ->addError(__('Extra Sheets'), $sheetName));
    }

    private function columns(): void
    {
        $this->sheets->all()->each(function ($sheet) {
            $templateHeader = $this->template->header($sheet->name());

            $this->missingColumns($sheet->name(), $sheet->header(), $templateHeader)
                ->extraColumns($sheet->name(), $sheet->header(), $templateHeader);
        });
    }

    private function missingColumns(
        string $sheetName,
        Collection $fileHeader,
        Collection $templateHeader
    ): self {
        $templateHeader->diff($fileHeader)
            ->each(fn ($column) => $this->summary
                ->addError(__('Missing Columns'), __('Sheet ":sheet", column ":column"', [
                    'sheet' => $sheetName, 'column' => $column,
                ])));

        return $this;
    }

    private function extraColumns(
        string $sheetName,
        Collection $fileHeader,
        Collection $templateHeader
    ): void {
        $fileHeader->diff($templateHeader)
            ->each(fn ($column) => $this->summary
                ->addError(__('Extra Columns'), __('Sheet ":sheet", column ":column"', [
                    'sheet' => $sheetName, 'column' => $column,
                ])));
    }
}
