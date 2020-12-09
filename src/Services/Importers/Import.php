<?php

namespace LaravelEnso\DataImport\Services\Importers;

use Closure;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use LaravelEnso\DataImport\Contracts\AfterHook;
use LaravelEnso\DataImport\Contracts\BeforeHook;
use LaravelEnso\DataImport\Enums\Statuses;
use LaravelEnso\DataImport\Jobs\Finalize;
use LaravelEnso\DataImport\Jobs\RejectedExport;
use LaravelEnso\DataImport\Jobs\Sheet;
use LaravelEnso\DataImport\Models\DataImport;
use LaravelEnso\DataImport\Services\Template;
use LaravelEnso\Helpers\Services\Obj;

class Import
{
    private DataImport $import;
    private string $sheet;
    private Template $template;
    private Batch $batch;

    public function __construct(DataImport $import, string $sheet)
    {
        $this->import = $import;
        $this->sheet = $sheet;
        $this->template = $import->template();
    }

    public function handle(): void
    {
        $this->prepare()
            ->beforeHook()
            ->dispatch();
    }

    private function prepare(): self
    {
        if ($this->import->waiting()) {
            $this->import->update(['status' => Statuses::Processing]);
        }

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

    public function dispatch(): self
    {
        $import = $this->import;
        $afterHook = $this->afterHook();
        $nextStep = $this->nextStep();

        $this->batch = Bus::batch([new Sheet($this->import, $this->sheet)])
            ->onQueue($this->template->queue())
            ->then(fn () => $import->update(['batch' => null]))
            ->then(fn ($batch) => $batch->cancelled() ? null : $afterHook())
            ->then(fn ($batch) => $batch->cancelled() ? null : $nextStep())
            ->name($this->sheet)
            ->dispatch();

        $this->import->update(['batch' => $this->batch->id]);

        return $this;
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
}
