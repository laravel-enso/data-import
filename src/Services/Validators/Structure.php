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
    private CSV|XLSX $reader;

    public function __construct(Template $template, string $path, string $filename)
    {
        $this->template = $template;
        $this->path = $path;
        $this->filename = $filename;
        $this->summary = new Summary();
        $this->reader = $this->reader();
    }

    public function validates(): bool
    {
        if ($this->template->isXLSX()) {
            $this->handleSheets();
        }

        if ($this->summary->errors()->isEmpty()) {
            if ($this->template->isXLSX()) {
                $this->template->sheets()->each(fn ($sheet) => $this
                    ->handleColumns($sheet));
            } else {
                $this->handleColumns($this->template->sheets());
            }
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
        return $this->template->isXLSX()
            ? new XLSX($this->path)
            : new CSV(
                $this->path,
                $this->template->delimiter(),
                $this->template->enclosure()
            );
    }

    private function handleSheets(): void
    {
        $template = $this->template->sheets()->pluck('name');
        $xlsx = $this->reader->sheets();

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
        if ($this->reader instanceof XLSX) {
            $iterator = $this->reader->rowIterator($sheet);
        } else {
            $iterator = $this->reader->rowIterator();
        }

        $header = Sanitize::header($iterator->current());
        $template = $this->template->header($sheet);

        $this->missingColumns($sheet, $header, $template)
            ->extraColumns($sheet, $header, $template);
    }

    private function missingColumns(string $sheet, Collection $header, Collection $template): self
    {
        \Log::info($template);
        \Log::info($header);
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
}
