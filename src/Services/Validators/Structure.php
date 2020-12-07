<?php

namespace LaravelEnso\DataImport\Services\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\DataImport\Services\Readers\XLSX;
use LaravelEnso\DataImport\Services\Sanitizers\Sanitize;
use LaravelEnso\DataImport\Services\Summary;
use LaravelEnso\DataImport\Services\Template;

class Structure
{
    private Template $template;
    private XLSX $xlsx;
    private Summary $summary;

    public function __construct(Template $template, string $path, string $filename)
    {
        $this->template = $template;
        $this->xlsx = new XLSX($path);
        $this->summary = new Summary($filename);
    }

    public function validates(): bool
    {
        $this->handleSheets();

        if ($this->summary->errors()->isEmpty()) {
            $this->xlsx->sheets()->each(fn ($sheet) => $this
                ->handleColumns($sheet));
        }

        return $this->summary->errors()->isEmpty();
    }

    public function summary(): array
    {
        return $this->summary->toArray();
    }

    private function handleSheets(): void
    {
        $template = $this->template->sheets()->pluck('name');
        $xlsx = $this->xlsx->sheets();

        $this->missingSheets($template, $xlsx)
            ->extraSheets($template, $xlsx);
    }

    private function missingSheets(Collection $template, Collection $xlsx): self
    {
        $template->diff($xlsx)->each(fn ($name) => $this->summary
            ->addError(__('Missing Sheets'), $name));

        return $this;
    }

    private function extraSheets(Collection $template, Collection $xlsx): void
    {
        $xlsx->diff($template)->each(fn ($name) => $this->summary
            ->addError(__('Extra Sheets'), $name));
    }

    private function handleColumns(string $sheet): void
    {
        $iterator = $this->xlsx->rowIterator($sheet);
        $xlsx = Sanitize::header($iterator->current());
        $template = $this->template->header($sheet);

        $this->missingColumns($sheet, $xlsx, $template)
            ->extraColumns($sheet, $xlsx, $template);
    }

    private function missingColumns(string $sheet, Collection $xlsx, Collection $template): self
    {
        $template->diff($xlsx)->each(fn ($column) => $this->summary
            ->addError(__('Missing Columns'), __('Sheet ":sheet", column ":column"', [
                'sheet' => $sheet, 'column' => $column,
            ])));

        return $this;
    }

    private function extraColumns(string $sheet, Collection $xlsx, Collection $template): void
    {
        $xlsx->diff($template)->each(fn ($column) => $this->summary
            ->addError(__('Extra Columns'), __('Sheet ":sheet", column ":column"', [
                'sheet' => $sheet, 'column' => $column,
            ])));
    }
}
