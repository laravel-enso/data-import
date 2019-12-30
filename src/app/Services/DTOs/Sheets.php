<?php

namespace LaravelEnso\DataImport\App\Services\DTOs;

use Illuminate\Support\Collection;

class Sheets
{
    private Collection $items;

    public function __construct()
    {
        $this->items = new Collection();
    }

    public function push(Sheet $sheet): void
    {
        $this->items->push($sheet);
    }

    public function all(): Collection
    {
        return $this->items;
    }

    public function names(): Collection
    {
        return $this->items
            ->map(fn ($sheet) => $sheet->name());
    }

    public function get(string $sheetName): Sheet
    {
        return $this->items
            ->first(fn ($sheet) => $sheet->name() === $sheetName);
    }
}
