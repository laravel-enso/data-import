<?php

namespace LaravelEnso\DataImport\app\Services\Importers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LaravelEnso\Core\app\Models\User;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\DataImport\app\Enums\Statuses;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Services\Template;
use LaravelEnso\DataImport\app\Contracts\AfterHook;
use LaravelEnso\DataImport\app\Jobs\RejectedExportJob;
use LaravelEnso\DataImport\app\Contracts\Authenticates;
use LaravelEnso\DataImport\app\Services\Writer\RejectedDump;
use LaravelEnso\DataImport\app\Services\Validators\Validation;

class Chunk
{
    private const UndeterminedImportError = 'Undetermined import error';

    private $dataImport;
    private $template;
    private $user;
    private $params;
    private $sheetName;
    private $chunk;
    private $rejected;
    private $errorColumn;
    private $validator;
    private $importer;

    public function __construct(DataImport $dataImport, Template $template, User $user, Obj $params, string $sheetName, Collection $chunk)
    {
        $this->dataImport = $dataImport;
        $this->template = $template;
        $this->user = $user;
        $this->params = $params;
        $this->sheetName = $sheetName;
        $this->chunk = $chunk;
        $this->rejected = collect();
        $this->errorColumn = config('enso.imports.errorColumn');
        $this->importer = $this->template->importer($sheetName);
        $this->validator = $this->template->customValidator($sheetName);
    }

    public function run()
    {
        $this->auth();

        $this->chunk->each(function ($row) {
            if ($this->validates($row)) {
                $this->import($row);
            }
        });

        $this->dumpRejected()
            ->updateProgress();

        if ($this->shouldEnd()) {
            $this->finalize();
        }
    }

    private function auth()
    {
        if ($this->importer instanceof Authenticates) {
            Auth::onceUsingId($this->user->id);
        }
    }

    private function validates($row)
    {
        (new Validation(
            $row,
            $this->template->validationRules($this->sheetName),
            $this->validator,
            $this->params
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
            $this->importer->run($row, $this->user, $this->params);
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
                $this->rejected
            ))->handle();
        }

        return $this;
    }

    private function updateProgress()
    {
        DB::transaction(function () {
            $this->dataImport = DataImport::whereId($this->dataImport->id)
                ->lockForUpdate()
                ->first();

            $this->dataImport->update([
                'successful' => $this->dataImport->successful + $this->successful(),
                'failed' => $this->dataImport->failed + $this->rejected->count(),
                'processed_chunks' => $this->dataImport->processed_chunks + 1,
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
            $this->importer->after($this->user, $this->params);
        }
    }

    private function successful()
    {
        return $this->chunk->count() - $this->rejected->count();
    }

    private function shouldEnd()
    {
        return $this->dataImport->fresh()->isFinalized();
    }
}
