<?php

namespace LaravelEnso\DataImport\app\Classes\Importers;

use LaravelEnso\DataImport\app\Enums\Statuses;
use LaravelEnso\DataImport\app\Classes\Template;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Jobs\ChunkImportJob;
use LaravelEnso\DataImport\app\Classes\Worksheet\Row;
use LaravelEnso\DataImport\app\Jobs\RejectedExportJob;
use LaravelEnso\DataImport\app\Classes\Reader\Content as Reader;

class Import
{
    private $import;
    private $template;
    private $worksheet;
    private $rowIterator;
    private $sheetName;
    private $header;
    private $chunkSize;
    private $chunk;
    private $chunkIndex;

    public function __construct(DataImport $import)
    {
        $this->import = $import;
        $this->template = new Template($import);
        $this->worksheet = (new Reader($this->import->file->path()));
        $this->chunkIndex = 0;
    }

    public function run()
    {
        // sleep(3);
        $this->initImport();

        $this->template->sheetNames()
            ->each(function ($sheetName) {
                $this->initParams($sheetName)
                    ->queueChunks();
            });

        $this->close();
    }

    private function initImport()
    {
        $this->import->update([
            'successful' => 0,
            'failed' => 0,
            'status' => Statuses::Processing
        ]);
    }

    private function initParams(string $sheetName)
    {
        $this->sheetName = $sheetName;
        $this->rowIterator = $this->worksheet->rowIterator($sheetName);
        $this->header = $this->template->header($sheetName);
        $this->chunkSize = $this->template->chunkSize($sheetName);

        return $this;
    }

    private function queueChunks()
    {
        // sleep(5);

        while (! $this->reachedFileEnd()) {
            $this->incChunks()
                ->prepareChunk()
                ->dispatch();
        }
    }

    private function incChunks()
    {
        $this->chunkIndex++;
        $this->import->update(['chunks' => $this->chunkIndex]);

        return $this;
    }

    private function prepareChunk()
    {
        $this->chunk = collect();

        while (! $this->reachedFileEnd() && $this->chunkIncomplete()) {
            $this->chunk->push($this->row());
            $this->rowIterator->next();
        }

        return $this;
    }

    private function row()
    {
        return new Row(
            $this->header->combine(
                $this->sanitizedRow()
                    ->pad($this->header->count(), null)
            )->toArray()
        );
    }

    private function dispatch()
    {
        ChunkImportJob::dispatch(
            $this->import,
            $this->template,
            $this->sheetName,
            $this->chunk,
            $this->chunkIndex,
            $this->reachedFileEnd()
        );
    }

    private function sanitizedRow()
    {
        return collect($this->rowIterator->current())
            ->map(function ($cell) {
                return ! is_string($cell)
                ? $cell
                : trim($cell) ?? null;
            });
    }

    private function chunkIncomplete()
    {
        return $this->chunk->count() < $this->chunkSize;
    }

    private function reachedFileEnd()
    {
        return ! $this->rowIterator->valid();
    }

    private function close()
    {
        $this->worksheet->close();

        if (config('queue.default') === 'sync') {
            $this->import->update(['status' => Statuses::Processed]);
            // sleep(5);
            RejectedExportJob::dispatch($this->import);
        }
    }
}
