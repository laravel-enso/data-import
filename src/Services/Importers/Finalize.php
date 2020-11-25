<?php

namespace LaravelEnso\DataImport\Services\Importers;

use LaravelEnso\Core\Models\User;
use LaravelEnso\DataImport\Contracts\AfterHook;
use LaravelEnso\DataImport\Models\DataImport;
use LaravelEnso\DataImport\Notifications\ImportDone;
use LaravelEnso\DataImport\Services\Template;
use LaravelEnso\Helpers\Services\Obj;

class Finalize
{
    private DataImport $dataImport;
    private User $user;
    private Obj $params;
    private Template $template;

    public function __construct(DataImport $dataImport, Template $template, User $user, Obj $params)
    {
        $this->dataImport = $dataImport;
        $this->user = $user;
        $this->params = $params;
        $this->template = $template;
    }

    public function handle(): void
    {
        $this->afterHook();

        $this->dataImport->endOperation();

        $this->notify();
    }

    private function afterHook(): void
    {
        $this->template->sheetNames()
            ->map(fn ($sheet) => $this->template->importer($sheet))
            ->filter(fn ($importer) => $importer instanceof AfterHook)
            ->each(fn (AfterHook $importer) => $importer->after($this->user, $this->params));
    }

    private function notify()
    {
        $this->dataImport->file->createdBy->notify(
            (new ImportDone($this->dataImport))
                ->onQueue(config('enso.imports.queues.notifications'))
        );
    }
}
