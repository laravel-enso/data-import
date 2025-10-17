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
    private Summary $summary;
    private CSV|XLSX $reader;

    public function __construct(
        private Template $template,
        private string $path,
        private string $filename,
        private string $extension,
    ) {
        $this->summary = new Summary();
        $this->reader = $this->reader();
    }

    public function validates(): bool
    {
        return $this->validatesExtension()
            ? $this->validatesStructure()
            : false;
    }

    private function validatesExtension(): bool
    {
        $valid = $this->template->isCSV()
            && in_array($this->extension, ['csv', 'txt'])
            || ! $this->template->isCSV()
            && $this->extension === 'xlsx';

        if (! $valid) {
            $required = $this->template->isCSV()
                ? '.csv / .txt'
                : '.xlsx';

            $message = 'Required ":required", Provided ":provided"';

            $this->summary->addError(__('File Extension'), __($message, [
                'required' => $required, 'provided' => $this->extension,
            ]));
        }

        return $valid;
    }

    private function validatesStructure(): bool
    {
        if (! $this->template->isCSV()) {
            $this->handleSheets();
        }

        if ($this->summary->errors()->isEmpty()) {
            $this->template->sheets()->pluck('name')->each(fn ($sheet) => $this
                ->handleColumns($sheet));
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
        return $this->template->isCSV()
            ? new CSV(
                $this->path,
                $this->template->delimiter(),
                $this->template->enclosure()
            )
            : new XLSX($this->path);
    }

    private function handleSheets(): void
    {
        $template = $this->template->sheets()->pluck('name');
        $xlsx = $this->reader->sheets();

        $this->missingSheets($template, $xlsx);

        if ($this->template->strict()) {
            $this->extraSheets($template, $xlsx);
        }
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
        $iterator = $this->reader->rowIterator($sheet);

        $header = Sanitize::header($iterator->current());
        $template = $this->template->header($sheet);

        $this->missingColumns($sheet, $header, $template);

        if ($this->template->strict()) {
            $this->extraColumns($sheet, $header, $template);
        }
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
}
