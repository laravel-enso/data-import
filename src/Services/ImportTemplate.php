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

    public function heading(): array
    {
        return $this->template->header($this->template->sheetNames()->first())->toArray();
    }

    public function rows(): array
    {
        return [
            $this->template->description($this->template->sheetNames()->first())->toArray(),
        ];
    }
}
