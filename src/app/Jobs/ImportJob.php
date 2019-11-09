<?php

namespace LaravelEnso\DataImport\app\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Services\Importers\Import;
use LaravelEnso\DataImport\app\Services\Template;

class ImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $dataImport;
    private $params;
    private $template;
    private $user;

    public $queue;
    public $timeout;

    public function __construct(DataImport $dataImport, Template $template, array $params = [])
    {
        $this->dataImport = $dataImport;
        $this->template = $template;
        $this->user = Auth::user();
        $this->params = $params;

        $this->queue = config('enso.imports.queues.splitting');
        $this->timeout = $template->timeout();
    }

    public function handle()
    {
        (new Import(
            $this->dataImport,
            $this->template,
            $this->user,
            $this->params
        ))->run();
    }
}
