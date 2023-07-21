<?php

namespace LaravelEnso\DataImport\Services\Importers;

use Illuminate\Bus\Batch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use LaravelEnso\DataImport\Jobs\Chunk as Job;
use LaravelEnso\DataImport\Models\Chunk;
use LaravelEnso\DataImport\Models\Import;
use LaravelEnso\DataImport\Services\Readers\CSV;
use LaravelEnso\DataImport\Services\Readers\XLSX;
use LaravelEnso\DataImport\Services\Sanitizers\Sanitize;
use LaravelEnso\Helpers\Exceptions\EnsoException;

class Sheet
{
    private int $chunkSize;
    private Collection $header;
    private XLSX|CSV $reader;

    private int $rowLength;
    private Chunk $chunk;

    public function __construct(
        private Batch $batch,
        private Import $import,
        private string $sheet
    ) {
        $this->chunkSize = $import->template()->chunkSize($this->sheet);
        $this->reader = $this->reader($this->import);
    }

    public function handle()
    {
        $this->init();

        while ($this->shouldBatchJobs()) {
            $this->prepare()
                ->dispatch();
        }
    }

    private function init(): void
    {
        $this->iterator = $this->reader->rowIterator($this->sheet);
        $this->header = Sanitize::header($this->iterator->current());
        $this->rowLength = $this->header->count();
        $this->iterator->next();
    }

    private function reader($import)
    {
        $file = Storage::path($import->file->path());

        return match ($import->file->extension()) {
            'csv' => new CSV($file),
            'xlsx' => new XLSX($file),
            default => throw new EnsoException('Unsupported import type'),
        };
    }

    private function prepare(): self
    {
        $this->chunk = $this->chunk();

        while ($this->shouldFillChunk()) {
            $this->addRow();
        }

        return $this;
    }

    private function addRow(): void
    {
        $cells = $this->iterator->current()->getCells();
        $row = Sanitize::cells($cells, $this->rowLength);
        $this->chunk->add($row);

        $this->iterator->next();
    }

    private function dispatch(): void
    {
        if (! $this->cancelled()) {
            $this->chunk->save();
            $this->batch->add(new Job($this->chunk));
        }
    }

    private function shouldBatchJobs(): bool
    {
        return ! $this->reachedSheetEnd()
            && ! $this->cancelled();
    }

    private function shouldFillChunk(): bool
    {
        return $this->chunk->count() < $this->chunkSize
            && ! $this->reachedSheetEnd();
    }

    private function cancelled(): bool
    {
        return $this->batch->fresh()->cancelled();
    }

    private function reachedSheetEnd(): bool
    {
        return ! $this->iterator->valid();
    }

    private function chunk(): Chunk
    {
        return Chunk::factory()->make([
            'import_id' => $this->import->id,
            'sheet' => $this->sheet,
            'header' => $this->header,
        ]);
    }
}
