<?php

namespace LaravelEnso\DataImport\Services\Importers;

use Box\Spout\Reader\XLSX\RowIterator;
use Closure;
use Illuminate\Bus\Batch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use LaravelEnso\DataImport\Contracts\AfterHook;
use LaravelEnso\DataImport\Contracts\BeforeHook;
use LaravelEnso\DataImport\Jobs\Chunk;
use LaravelEnso\DataImport\Jobs\Finalize;
use LaravelEnso\DataImport\Jobs\RejectedExport;
use LaravelEnso\DataImport\Models\DataImport;
use LaravelEnso\DataImport\Services\DTOs\Chunk as DTO;
use LaravelEnso\DataImport\Services\DTOs\Row;
use LaravelEnso\DataImport\Services\Readers\XLSX;
use LaravelEnso\DataImport\Services\Sanitizers\Sanitize;
use LaravelEnso\DataImport\Services\Template;
use LaravelEnso\Helpers\Services\Obj;

class Import
{
    private DataImport $import;
    private string $sheet;
    private Template $template;
    private XLSX $xlsx;
    private RowIterator $iterator;
    private Collection $header;
    private DTO $chunk;
    private Batch $batch;
    private int $limit;

    public function __construct(DataImport $import, string $sheet)
    {
        $this->import = $import;
        $this->sheet = $sheet;
        $this->template = $import->template();
        $this->xlsx = new XLSX($import->file->path());
        $this->limit = $this->template->chunkSize($this->sheet);
    }

    public function handle(): void
    {
        $this->prepare()
            ->beforeHook()
            ->initBatch()
            ->batchJobs();
    }

    private function prepare(): self
    {
        if ($this->import->waiting()) {
            $this->import->startProcessing();
        }

        $this->header = $this->xlsx->header($this->sheet);
        $sheet = $this->xlsx->sheet($this->sheet);
        $this->iterator = $this->xlsx->rowIterator($sheet);
        $this->iterator->next();

        return $this;
    }

    private function beforeHook(): self
    {
        $importer = $this->template->importer($this->sheet);

        if ($importer instanceof BeforeHook) {
            $importer->before(
                $this->import->createdBy,
                new Obj($this->import->params)
            );
        }

        return $this;
    }

    public function initBatch(): self
    {
        $import = $this->import;
        $afterHook = $this->afterHook();
        $nextStep = $this->nextStep();

        $this->batch = Bus::batch([])
            ->onQueue($this->import->template()->queue())
            ->then(fn () => $afterHook())
            ->then(fn () => $import->update(['batch' => null]))
            ->then(fn () => $nextStep())
            ->name($this->sheet)
            ->dispatch();

        $this->import->update(['batch' => $this->batch->id]);

        return $this;
    }

    private function batchJobs(): self
    {
        while ($this->shouldBatchJobs()) {
            $this->prepareJob()
                ->dispatch();
        }

        return $this;
    }

    private function prepareJob(): self
    {
        $this->import->increment('chunks');
        $this->chunk = new DTO($this->sheet, $this->limit);

        while ($this->shouldFillChunk()) {
            $this->addRow();
        }

        return $this;
    }

    private function addRow(): void
    {
        $row = new Row(
            $this->header->combine($this->sanitizedRow())->toArray()
        );

        if ($row->content()->isNotEmpty()) {
            $this->chunk->push($row);
        }

        $this->iterator->next();
    }

    private function dispatch(): void
    {
        $this->batch->add(
            new Chunk($this->import, $this->chunk)
        );
    }

    private function afterHook(): Closure
    {
        $importer = $this->template->importer($this->sheet);
        $user = $this->import->createdBy;
        $params = new Obj($this->import->params);

        return fn () => $importer instanceof AfterHook
            ? $importer->after($user, $params)
            : null;
    }

    public function nextStep(): Closure
    {
        $import = $this->import;
        $sheet = $this->sheet;
        $nextSheet = $import->template()->nextSheet($sheet);

        if ($nextSheet) {
            return fn () => $import->import($nextSheet->get('name'));
        } else {
            return fn () => RejectedExport::withChain([new Finalize($import)])
                ->dispatch($import);
        }
    }

    private function sanitizedRow(): Collection
    {
        $length = $this->header->count();
        $cells = $this->iterator->current()->getCells();

        return Sanitize::handle($cells, $length);
    }

    private function shouldBatchJobs(): bool
    {
        return ! $this->reachedSheetEnd()
            && ! $this->import->fresh()->cancelled(); //TODO batch->cancelled
    }

    private function shouldFillChunk(): bool
    {
        return ! $this->chunk->full() && ! $this->reachedSheetEnd();
    }

    private function reachedSheetEnd(): bool
    {
        return ! $this->iterator->valid();
    }
}
