<?php

namespace LaravelEnso\DataImport\Services\Writer;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use LaravelEnso\DataImport\Models\DataImport;

class RejectedDump
{
    private DataImport $dataImport;
    private string $sheetName;
    private Collection $rejected;
    private int $index;
    private Collection $dump;

    public function __construct(DataImport $dataImport, string $sheetName, Collection $rejected, int $index)
    {
        $this->dataImport = $dataImport;
        $this->sheetName = $sheetName;
        $this->rejected = $rejected;
        $this->index = $index;
    }

    public function handle(): void
    {
        $this->prepare()
            ->store();
    }

    private function prepare(): self
    {
        $this->dump = (new Collection([$this->sheetName, $this->header()]))
            ->merge($this->values());

        return $this;
    }

    private function store(): void
    {
        Storage::put($this->path(), $this->dump->toJson());
    }

    private function header(): Collection
    {
        return $this->rejected->first()->keys();
    }

    private function values(): Collection
    {
        return $this->rejected->map(fn ($row) => $row->values());
    }

    private function path(): string
    {
        return $this->dataImport->rejectedFolder()
            .DIRECTORY_SEPARATOR
            ."rejected_dump_{$this->index}.json";
    }
}
