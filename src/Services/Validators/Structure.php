<?php

namespace LaravelEnso\DataImport\Services\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\DataImport\Services\Readers\CSV;
use LaravelEnso\DataImport\Services\Readers\XLSX;
use LaravelEnso\DataImport\Services\Sanitizers\Sanitize;
use LaravelEnso\DataImport\Services\Summary;
use LaravelEnso\DataImport\Services\Template;

class Structure
{
    private Template $template;
    private Summary $summary;
    private string $path;
    private string $filename;
    private string $extension;

    public function __construct(Template $template, string $path, string $filename, string $extension)
    {
        $this->template = $template;
        $this->path = $path;
        $this->filename = $filename;
        $this->extension = $extension;
        $this->summary = new Summary();
    }

    public function validates(): bool
    {
        if (! $this->isCSV()) {
            $this->handleSheets();
        }

        if ($this->summary->errors()->isEmpty()) {
            $this->isCSV()
                ? $this->handleColumns($this->template->sheets()
                ->pluck('name')->first())
                : $this->reader()->sheets()
                ->each(fn ($sheet) => $this->handleColumns($sheet));
        }

        return $this->summary->errors()->isEmpty();
    }

    public function summary(): array
    {
        return [
            'filename' => $this->filename,
            'errors' => $this->summary->toArray(),
        ];
    }

    private function reader(): CSV|XLSX
    {
        return match ($this->extension) {
            'csv' => new CSV($this->path),
            default => new XLSX($this->path),
        };
    }

    private function handleSheets(): void
    {
        $template = $this->template->sheets()->pluck('name');
        $reader = $this->reader()->sheets();

        $this->missingSheets($template, $reader)
            ->extraSheets($template, $reader);
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
        $iterator = $this->reader()->rowIterator($sheet);
        $header = Sanitize::header($iterator->current());
        $template = $this->template->header($sheet);

        $this->missingColumns($sheet, $header, $template)
            ->extraColumns($sheet, $header, $template);
    }

    private function missingColumns(string $sheet, Collection $header, Collection $template): self
    {
        $template->diff($header)->each(fn ($column) => $this->summary
            ->addError(__('Missing Columns'), __('Sheet ":sheet", column ":column"', [
                'sheet' => $sheet, 'column' => $column,
            ])));

        return $this;
    }

    private function extraColumns(string $sheet, Collection $header, Collection $template): void
    {
        $header->diff($template)->each(fn ($column) => $this->summary
            ->addError(__('Extra Columns'), __('Sheet ":sheet", column ":column"', [
                'sheet' => $sheet, 'column' => $column,
            ])));
    }

    private function isCSV(): bool
    {
        return $this->extension === 'csv';
    }
}
