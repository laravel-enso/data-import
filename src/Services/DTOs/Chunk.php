<?php

namespace LaravelEnso\DataImport\Services\DTOs;

use Closure;
use Illuminate\Support\Collection;
use LaravelEnso\DataImport\Models\RejectedChunk;

class Chunk
{
    private string $sheet;
    private int $limit;
    private Collection $items;
    private RejectedChunk $rejected;

    public function __construct(string $sheet, int $limit)
    {
        $this->sheet = $sheet;
        $this->limit = $limit;
        $this->items = new Collection();
        $this->rejected = RejectedChunk::factory()->make(['sheet' => $sheet]);
    }

    public function sheet(): string
    {
        return $this->sheet;
    }

    public function full(): bool
    {
        return $this->limit === $this->items->count();
    }

    public function rejected(): RejectedChunk
    {
        return $this->rejected;
    }

    public function successful(): int
    {
        return $this->items->count() - $this->rejected->count();
    }

    public function failed(): int
    {
        return $this->rejected->count();
    }

    public function push(Row $row)
    {
        $this->items->push($row);
    }

    public function reject(Row $row)
    {
        $this->rejected->add($row);
    }

    public function each(Closure $closure)
    {
        return $this->items->each($closure);
    }
}
