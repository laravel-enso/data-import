<?php

namespace LaravelEnso\DataImport\Services\Importers;

use Box\Spout\Reader\XLSX\RowIterator;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Collection;
use LaravelEnso\Core\Models\User;
use LaravelEnso\DataImport\Contracts\BeforeHook;
use LaravelEnso\DataImport\Enums\Statuses;
use LaravelEnso\DataImport\Jobs\ChunkImport;
use LaravelEnso\DataImport\Models\DataImport;
use LaravelEnso\DataImport\Services\DTOs\Row;
use LaravelEnso\DataImport\Services\DTOs\Sheets;
use LaravelEnso\DataImport\Services\Readers\XLSX;
use LaravelEnso\DataImport\Services\Template;
use LaravelEnso\Helpers\Services\Obj;

class Import
{
    private DataImport $dataImport;
    private Template $template;
    private Sheets $sheets;
    private User $user;
    private Obj $params;
    private XLSX $xlsx;
    private RowIterator $rowIterator;
    private Collection $header;
    private Collection $chunk;
    private string $sheetName;

    public function __construct(DataImport $dataImport, Template $template, Sheets $sheets, User $user, Obj $params)
    {
        $this->dataImport = $dataImport;
        $this->template = $template;
        $this->sheets = $sheets;
        $this->user = $user;
        $this->params = $params;
        $this->xlsx = new XLSX($dataImport->file->path());
    }

    public function run(): void
    {
        $this->dataImport->startProcessing();

        $this->template->sheetNames()
            ->each(fn ($sheetName) => $this->prepareSheet($sheetName)
                ->beforeHook()
                ->queueChunks());

        $this->xlsx->close();
    }

    private function prepareSheet(string $sheetName): self
    {
        $this->sheetName = $sheetName;
        $this->header = $this->sheets->get($this->sheetName)->header();
        $this->rowIterator = $this->xlsx->rowIteratorFor($this->sheetName);

        return $this;
    }

    private function beforeHook(): self
    {
        $importer = $this->template->importer($this->sheetName);

        if ($importer instanceof BeforeHook) {
            $importer->before($this->user, $this->params);
        }

        return $this;
    }

    private function queueChunks(): void
    {
        while (! $this->sheetFinalized() && ! $this->wasCanceled()) {
            $this->prepareChunk()
                ->fileParseStatus()
                ->dispatch();
        }
    }

    private function prepareChunk(): self
    {
        $this->dataImport->increment('chunks');

        $this->chunk = new Collection();

        while ($this->chunkIncomplete() && ! $this->sheetFinalized()) {
            $this->addRow();
        }

        return $this;
    }

    private function addRow(): void
    {
        $row = $this->row();

        if ($row->hasContent()) {
            $this->chunk->push($row);
        }

        $this->rowIterator->next();
    }

    private function row(): Row
    {
        return new Row(
            $this->header->combine(
                $this->sanitizeRow()
            )->toArray()
        );
    }

    private function fileParseStatus(): self
    {
        if ($this->fileFinalized()) {
            $this->dataImport->update(['file_parsed' => true]);
        }

        return $this;
    }

    private function dispatch(): void
    {
        ChunkImport::dispatch(
            $this->dataImport,
            $this->template,
            $this->user,
            $this->params,
            $this->sheetName,
            $this->chunk,
        );
    }

    private function sanitizeRow(): Collection
    {
        $count = $this->header->count();

        return (new Collection($this->rowIterator->current()->getCells()))
            ->map(fn ($cell) => $this->sanitizeCell($cell->getValue()))
            ->slice(0, $count)
            ->pad($count, null);
    }

    private function sanitizeCell($cell)
    {
        if ($cell instanceof DateTime) {
            return Carbon::instance($cell)->toDateTimeString();
        }

        if (is_string($cell)) {
            $cell = trim($cell);
        }

        return $cell === '' ? null : $cell;
    }

    private function chunkIncomplete(): bool
    {
        return $this->chunk->count() < $this->template->chunkSize($this->sheetName);
    }

    private function fileFinalized(): bool
    {
        return $this->sheetFinalized()
            && $this->sheetName === $this->template->sheetNames()->last();
    }

    private function sheetFinalized(): bool
    {
        return ! $this->rowIterator->valid();
    }

    private function wasCanceled(): bool
    {
        return $this->dataImport->fresh()->status === Statuses::Canceled;
    }
}
