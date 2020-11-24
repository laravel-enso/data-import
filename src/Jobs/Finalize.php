<?php

namespace LaravelEnso\DataImport\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use LaravelEnso\Core\Models\User;
use LaravelEnso\DataImport\Models\DataImport;
use LaravelEnso\DataImport\Services\Importers\Finalize as Service;
use LaravelEnso\DataImport\Services\Template;
use LaravelEnso\Helpers\Services\Obj;

class Finalize implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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

        $this->queue = config('enso.imports.queues.rejected');
    }

    public function handle()
    {
        (new Service(
            $this->dataImport, $this->template, $this->user, $this->params
        ))->handle();
    }
}
