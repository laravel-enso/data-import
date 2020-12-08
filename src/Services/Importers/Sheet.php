<?php

namespace LaravelEnso\DataImport\Services\Importers;

use Illuminate\Bus\Batch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use LaravelEnso\DataImport\Jobs\Chunk as Job;
use LaravelEnso\DataImport\Models\Chunk;
use LaravelEnso\DataImport\Models\DataImport;
use LaravelEnso\DataImport\Services\Readers\XLSX;
use LaravelEnso\DataImport\Services\Sanitizers\Sanitize;

class Sheet
{
    private Batch $batch;
    private DataImport $import;
    private string $sheet;
    private int $chunkSize;
    private XLSX $xlsx;
    private Collection $header;
    private int $rowLength;
    private Chunk $chunk;

    public function __construct(Batch $batch, DataImport $import, string $sheet)
    {
        $this->batch = $batch;
        $this->import = $import;
        $this->sheet = $sheet;
        $this->chunkSize = $import->template()->chunkSize($this->sheet);
        $this->xlsx = new XLSX(Storage::path($import->file->path));
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
        $this->iterator = $this->xlsx->rowIterator($this->sheet);
        $this->header = Sanitize::header($this->iterator->current());
        $this->rowLength = $this->header->count();
        $this->iterator->next();
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
