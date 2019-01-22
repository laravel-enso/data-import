<?php

namespace LaravelEnso\DataImport\app\Classes\Importers;

use DateTime;
use Carbon\Carbon;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\DataImport\app\Enums\Statuses;
use LaravelEnso\DataImport\app\Classes\Template;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Contracts\AfterHook;
use LaravelEnso\DataImport\app\Jobs\ChunkImportJob;
use LaravelEnso\DataImport\app\Contracts\BeforeHook;
use LaravelEnso\DataImport\app\Classes\Worksheet\Row;
use LaravelEnso\DataImport\app\Jobs\RejectedExportJob;
use LaravelEnso\DataImport\app\Classes\Reader\Content as Reader;

class Import
{
    private $dataImport;
    private $params;
    private $template;
    private $reader;
    private $rowIterator;
    private $sheetName;
    private $header;
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
            'status' => Statuses::Processing,
        ]);
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
        $this->rowIterator = $this->reader->rowIterator($this->sheetName);
        $this->header = $this->template->header($this->sheetName);
        $this->chunkSize = $this->template->chunkSize($this->sheetName);

        return $this;
    }

    private function queueChunks()
    {
        while (! $this->hasFinished()) {
            $this->prepareChunk()
                ->dispatch();
        }
    }

    private function prepareChunk()
    {
        $this->chunkIndex++;
        $this->chunk = collect();

        while (! $this->hasFinished() && $this->chunkIsIncomplete()) {
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
            $this->dataImport,
            $this->params,
            $this->template,
            $this->sheetName,
            $this->chunk,
            $this->chunkIndex,
            $this->hasFinished()
        );
    }

    private function sanitizedRow()
    {
        return collect($this->rowIterator->current())
            ->map(function ($cell) {
                if ($cell instanceof DateTime) {
                    return Carbon::instance($cell)->toDateTimeString();
                }

                return ! is_string($cell)
                    ? $cell
                    : trim($cell) ?? null;
            });
    }

    private function chunkIsIncomplete()
    {
        return $this->chunk->count() < $this->chunkSize;
    }

    private function hasFinished()
    {
        return ! $this->rowIterator->valid();
    }

    private function closeReader()
    {
        $this->reader->close();
        unset($this->reader);

        return $this;
    }

    private function finalize()
    {
        if (config('queue.default') === 'sync') {
            $this->afterHook();
            $this->dataImport->update(['status' => Statuses::Processed]);
            RejectedExportJob::dispatch($this->dataImport);
        }
    }

    private function afterHook()
    {
        $importer = $this->template->importer($this->sheetName);

        if ($importer instanceof AfterHook) {
            $importer->after($this->params);
        }
    }
}
