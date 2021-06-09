<?php

namespace LaravelEnso\DataImport\Services\Importers;

use Closure;
use Illuminate\Support\Facades\Bus;
use LaravelEnso\DataImport\Contracts\AfterHook;
use LaravelEnso\DataImport\Contracts\BeforeHook;
use LaravelEnso\DataImport\Enums\Statuses;
use LaravelEnso\DataImport\Jobs\Finalize;
use LaravelEnso\DataImport\Jobs\RejectedExport;
use LaravelEnso\DataImport\Jobs\Sheet;
use LaravelEnso\DataImport\Models\DataImport;
use LaravelEnso\DataImport\Services\Template;

class Import
{
    private Template $template;

    public function __construct(
        private DataImport $import,
        private string $sheet
    ) {
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
            $importer->before($this->import);
        }

        return $this;
    }

    public function dispatch(): self
    {
        $import = $this->import;
        $afterHook = $this->afterHook();
        $nextStep = $this->nextStep();

        $batch = Bus::batch([new Sheet($this->import, $this->sheet)])
            ->onQueue($this->template->queue())
            ->then(fn () => $import->update(['batch' => null]))
            ->then(fn ($batch) => $batch->cancelled() ? null : $afterHook())
            ->then(fn ($batch) => $batch->cancelled() ? null : $nextStep())
            ->name($this->sheet)
            ->dispatch();

        $this->import->update(['batch' => $batch->id]);

        return $this;
    }

    private function afterHook(): Closure
    {
        $importer = $this->template->importer($this->sheet);

        return fn () => $importer instanceof AfterHook
            ? $importer->after($this->import)
            : null;
    }

    public function nextStep(): Closure
    {
        $import = $this->import;
        $sheet = $this->sheet;
        $nextSheet = $this->template->nextSheet($sheet);

        if ($nextSheet) {
            return fn () => $import->import($nextSheet->get('name'));
        } else {
            return fn () => RejectedExport::withChain([new Finalize($import)])
                ->dispatch($import);
        }
    }
}
