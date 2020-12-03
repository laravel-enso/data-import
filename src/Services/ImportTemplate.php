<?php

namespace LaravelEnso\DataImport\Services;

use LaravelEnso\Excel\Contracts\ExportsExcel;

class ImportTemplate implements ExportsExcel
{
    private Template $template;
    private string $type;

    public function __construct(string $type)
    {
        $this->template = new Template($type);
        $this->type = $type;
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
        return [$this->template->descriptions($sheet)->toArray()];
    }

    public function sheets(): array
    {
        return $this->template->sheets()->pluck('name')->toArray();
    }
}
