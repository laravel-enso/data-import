<?php

namespace LaravelEnso\DataImport\Services;

use LaravelEnso\Excel\Contracts\ExportsExcel;

class ImportTemplate implements ExportsExcel
{
    private Template $template;

    public function __construct(private string $type)
    {
        $this->template = new Template($this->type);
    }

    public function filename(): string
    {
        return "{$this->type}.xlsx";
    }

    public function heading(string $sheet): array
    {
        return $this->template->header($sheet)->toArray();
    }

    public function rows(string $sheet): array
    {
        return [
            $this->template->validations($sheet)->toArray(),
            $this->template->descriptions($sheet)->toArray(),
        ];
    }

    public function sheets(): array
    {
        return $this->template->sheets()->pluck('name')->toArray();
    }
}
