<?php

namespace LaravelEnso\DataImport\app\Classes\Importers;

use Illuminate\Support\Collection;
use LaravelEnso\DataImport\app\Enums\Statuses;
use LaravelEnso\DataImport\app\Classes\Template;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Jobs\RejectedExportJob;
use LaravelEnso\DataImport\app\Classes\Writer\RejectedDump;
use LaravelEnso\DataImport\app\Classes\Validators\Validation;

class Chunk
{
    private const UndeterminedImportError = 'Undetermined import error';

    private $import;
    private $template;
    private $sheetName;
    private $chunk;
    private $index;
    private $isLast;
    private $rejected;
    private $errorColumn;
    private $successful;

    public function __construct(DataImport $import, Template $template, string $sheetName, Collection $chunk, int $index, bool $isLast)
    {
        $this->import = $import;
        $this->template = $template;
        $this->sheetName = $sheetName;
        $this->chunk = $chunk;
        $this->index = $index;
        $this->isLast = $isLast;
        $this->rejected = collect();
        $this->errorColumn = config('enso.imports.errorColumn');
        $this->successful = 0;
    }

    public function run()
    {
        // sleep(1);
        $this->chunk->each(function ($row) {
            if ($this->validates($row)) {
                $this->import($row);
            }
        });

        $this->dumpRejected()
            ->updateStats();

        if ($this->isLast()) {
            $this->import->update(['status' => Statuses::Processed]);
            RejectedExportJob::dispatch($this->import);
        }
    }

    private function import($row)
    {
        try {
            $this->template
                ->importer($this->sheetName)
                ->run($row);

            $this->successful++;
        } catch (\Exception $exception) {
            $row->set($this->errorColumn, self::UndeterminedImportError); //TODO add config
            $this->rejected->push($row);
            \Log::debug($exception->getTrace());
        }
    }

    private function validates($row)
    {
        (new Validation(
            $row,
            $this->template->validationRules($this->sheetName),
            $this->template->validator($this->sheetName)
        ))->run();

        if ($row->has($this->errorColumn)) {
            $this->rejected->push($row);
        }

        return ! $row->has($this->errorColumn);
    }

    private function dumpRejected()
    {
        if ($this->rejected->isNotEmpty()) {
            (new RejectedDump(
                $this->import, $this->sheetName, $this->rejected, $this->index
            ))->handle();
        }

        return $this;
    }

    private function updateStats()
    {
        \DB::transaction(function () {
            $this->import = DataImport::whereId($this->import->id)
                ->lockForUpdate()
                ->first();

            $this->import->update([
                'successful' => $this->import->successful + $this->successful,
                'failed' => $this->import->failed + $this->rejected->count(),
            ]);
        });
    }

    private function isLast()
    {
        return config('queue.default') !== 'sync'
            && $this->isLast;
    }
}
