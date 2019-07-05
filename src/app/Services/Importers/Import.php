<?php

namespace LaravelEnso\DataImport\app\Services\Importers;

use DateTime;
use Carbon\Carbon;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\DataImport\app\Enums\Statuses;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Services\Template;
use LaravelEnso\DataImport\app\Contracts\AfterHook;
use LaravelEnso\DataImport\app\Jobs\ChunkImportJob;
use LaravelEnso\DataImport\app\Contracts\BeforeHook;
use LaravelEnso\DataImport\app\Jobs\RejectedExportJob;
use LaravelEnso\DataImport\app\Services\Worksheet\Row;
use LaravelEnso\DataImport\app\Services\Reader\Content as Reader;

class Import
{
    private $dataImport;
    private $params;
    private $template;
    private $reader;
    private $rowIterator;
    private $sheetName;
    private $header;
    private $headerCount;
    private $chunkSize;
    private $chunk;
    private $chunkIndex;

    public function __construct(DataImport $dataImport, Template $template, array $params)
    {
        $this->dataImport = $dataImport;
        $this->params = new Obj($params);
        $this->template = $template;
        $this->chunkIndex = 0;
    }

    public function run()
    {
        $this->openReader()
            ->start();

        $this->template->sheetNames()
            ->each(function ($sheetName) {
                $this->sheetName = $sheetName;

                $this->beforeHook()
                    ->prepareSheet()
                    ->queueChunks();
            });

        $this->closeReader()
            ->finalize();
    }

    private function openReader()
    {
        $this->reader = new Reader(
            $this->dataImport->file->path()
        );

        $this->reader->open();

        return $this;
    }

    private function start()
    {
        $this->dataImport->update([
            'successful' => 0,
            'failed' => 0,
        ]);

        $this->dataImport->startProcessing();
    }

    private function beforeHook()
    {
        $importer = $this->template->importer($this->sheetName);

        if ($importer instanceof BeforeHook) {
            $importer->before($this->params);
        }

        return $this;
    }

    private function prepareSheet()
    {
        $this->header = collect($this->reader->header($this->sheetName));
        $this->headerCount = $this->header->count();
        $this->rowIterator = $this->reader->rowIterator($this->sheetName);
        $this->chunkSize = $this->template->chunkSize($this->sheetName);

        return $this;
    }

    private function queueChunks()
    {
        while (! $this->sheetHasFinished()) {
            $this->prepareChunk()
                ->dispatch();
        }
    }

    private function prepareChunk()
    {
        $this->chunkIndex++;
        $this->chunk = collect();

        while (! $this->sheetHasFinished() && $this->chunkIsIncomplete()) {
            $row = $this->row();

            if ($row->isNotEmpty()) {
                $this->chunk->push($row);
            }

            $this->rowIterator->next();
        }

        return $this;
    }

    private function row()
    {
        return new Row(
            $this->header->combine(
                $this->sanitizeRow()
            )->toArray()
        );
    }

    private function dispatch()
    {
        ChunkImportJob::dispatch(
            $this->dataImport,
            $this->params,
            $this->template,
            $this->sheetName,
            $this->chunk,
            $this->chunkIndex,
            $this->fileHasFinished()
        );
    }

    private function sanitizeRow()
    {
        return collect($this->rowIterator->current())
            ->map(function ($cell) {
                return $this->sanitizeCell($cell);
            })->slice(0, $this->headerCount)
            ->pad($this->headerCount, null);
    }

    private function sanitizeCell($cell)
    {
        if ($cell instanceof DateTime) {
            return Carbon::instance($cell)->toDateTimeString();
        }

        if (! is_string($cell)) {
            return $cell;
        }

        $cell = trim($cell);

        return empty($cell) ? null : $cell;
    }

    private function chunkIsIncomplete()
    {
        return $this->chunk->count() < $this->chunkSize;
    }

    private function sheetHasFinished()
    {
        return ! $this->rowIterator->valid();
    }

    private function fileHasFinished()
    {
        return $this->sheetHasFinished()
            && $this->sheetName === $this->template->sheetNames()->last();
    }

    private function closeReader()
    {
        $this->reader->close();
        unset($this->reader);

        return $this;
    }

    private function finalize()
    {
        if (config('queue.default') !== 'sync') {
            return;
        }

        $this->afterHook();

        $this->dataImport->setStatus(Statuses::Processed);

        RejectedExportJob::dispatch($this->dataImport);
    }

    private function afterHook()
    {
        $importer = $this->template->importer($this->sheetName);

        if ($importer instanceof AfterHook) {
            $importer->after($this->params);
        }
    }
}
