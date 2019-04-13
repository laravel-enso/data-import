<?php

namespace LaravelEnso\DataImport\app\Classes\Importers;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\DataImport\app\Enums\Statuses;
use LaravelEnso\DataImport\app\Classes\Template;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Contracts\AfterHook;
use LaravelEnso\DataImport\app\Jobs\RejectedExportJob;
use LaravelEnso\DataImport\app\Classes\Writer\RejectedDump;
use LaravelEnso\DataImport\app\Classes\Validators\Validation;

class Chunk
{
    private const UndeterminedImportError = 'Undetermined import error';

    private $dataImport;
    private $template;
    private $sheetName;
    private $chunk;
    private $index;
    private $isLast;
    private $rejected;
    private $errorColumn;
    private $validator;
    private $importer;

    public function __construct(DataImport $dataImport, Obj $params, Template $template, string $sheetName, Collection $chunk, int $index, bool $isLast)
    {
        $this->dataImport = $dataImport;
        $this->params = $params;
        $this->template = $template;
        $this->sheetName = $sheetName;
        $this->chunk = $chunk;
        $this->index = $index;
        $this->isLast = $isLast;
        $this->rejected = collect();
        $this->errorColumn = config('enso.imports.errorColumn');
        $this->importer = $this->template->importer($sheetName);
        $this->validator = $this->template->customValidator($sheetName);
    }

    public function run()
    {
        $this->chunk->each(function ($row) {
            if ($this->validates($row)) {
                $this->import($row);
            }
        });

        $this->dumpRejected()
            ->updateProgress();

        if ($this->isLast()) {
            $this->finalize();
        }
    }

    private function validates($row)
    {
        (new Validation(
            $row,
            $this->template->validationRules($this->sheetName),
            $this->validator
        ))->run();

        if ($row->isRejected()) {
            $this->rejected->push($row);
        }

        if ($this->validator) {
            $this->validator->emptyErrors();
        }

        return ! $row->isRejected();
    }

    private function import($row)
    {
        try {
            $this->importer->run($row, $this->params);
        } catch (\Exception $exception) {
            $row->set($this->errorColumn, self::UndeterminedImportError);
            $this->rejected->push($row);
            \Log::debug($exception->getMessage());
        }
    }

    private function dumpRejected()
    {
        if ($this->rejected->isNotEmpty()) {
            (new RejectedDump(
                $this->dataImport,
                $this->sheetName,
                $this->rejected,
                $this->index
            ))->handle();
        }

        return $this;
    }

    private function updateProgress()
    {
        \DB::transaction(function () {
            $this->dataImport = DataImport::whereId($this->dataImport->id)
                ->lockForUpdate()
                ->first();

            $this->dataImport->update([
                'successful' => $this->dataImport->successful + $this->successful(),
                'failed' => $this->dataImport->failed + $this->rejected->count(),
            ]);
        });
    }

    private function finalize()
    {
        $this->afterHook();

        $this->dataImport->setStatus(Statuses::Processed);

        RejectedExportJob::dispatch($this->dataImport);
    }

    private function afterHook()
    {
        if ($this->importer instanceof AfterHook) {
            $this->importer->after($this->params);
        }
    }

    private function successful()
    {
        return $this->chunk->count() - $this->rejected->count();
    }

    private function isLast()
    {
        return config('queue.default') !== 'sync'
            && $this->isLast;
    }
}
