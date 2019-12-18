<?php

namespace LaravelEnso\DataImport\app\Services\Importers;

use Carbon\Carbon;
use DateTime;
use LaravelEnso\Core\app\Models\User;
use LaravelEnso\DataImport\app\Contracts\BeforeHook;
use LaravelEnso\DataImport\app\Jobs\ChunkImportJob;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Services\Reader\Content as Reader;
use LaravelEnso\DataImport\app\Services\Template;
use LaravelEnso\DataImport\app\Services\Worksheet\Row;
use LaravelEnso\Helpers\app\Classes\Obj;

class Import
{
    private $dataImport;
    private $params;
    private $template;
    private $user;
    private $reader;
    private $rowIterator;
    private $sheetName;
    private $header;
    private $headerCount;
    private $chunkSize;
    private $chunk;

    public function __construct(DataImport $dataImport, Template $template, User $user, array $params)
    {
        $this->dataImport = $dataImport;
        $this->template = $template;
        $this->user = $user;
        $this->params = new Obj($params);
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

        $this->closeReader();
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
        $this->dataImport->startProcessing();
    }

    private function beforeHook()
    {
        $importer = $this->template->importer($this->sheetName);

        if ($importer instanceof BeforeHook) {
            $importer->before($this->user, $this->params);
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
        $this->dataImport->increment('chunks');

        $this->chunk = collect();

        while ($this->chunkIsIncomplete()) {
            $row = $this->row();

            if ($row->isNotEmpty()) {
                $this->chunk->push($row);
            }

            $this->rowIterator->next();

            if ($this->sheetHasFinished()) {
                if ($this->fileHasFinished()) {
                    $this->dataImport->update(['file_parsed' => true]);
                }

                break;
            }
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
            $this->template,
            $this->user,
            $this->params,
            $this->sheetName,
            $this->chunk,
        );
    }

    private function sanitizeRow()
    {
        return collect($this->rowIterator->current()->getCells())
            ->map(function ($cell) {
                return $this->sanitizeCell($cell->getValue());
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
}
