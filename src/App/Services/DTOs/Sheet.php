<?php

namespace LaravelEnso\DataImport\App\Services\DTOs;

use Illuminate\Support\Collection;

class Sheet
{
    private string $name;
    private Collection $header;

    public function __construct(string $name, Collection $header)
    {
        $this->name = $name;
        $this->header = $header;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function header(): Collection
    {
        return $this->header;
    }
}
