<?php

namespace LaravelEnso\DataImport\app\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use LaravelEnso\DataImport\app\Classes\Template;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Classes\Importers\Import;

class ImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $dataImport;
    private $params;
    private $template;

    public function __construct(DataImport $dataImport, Template $template, array $params = [])
    {
        $this->dataImport = $dataImport;
        $this->params = $params;
        $this->template = $template;
        $this->timeout = $template->timeout();
        $this->queue = config('enso.imports.queues.splitting');
    }

    public function handle()
    {
        (new Import($this->dataImport, $this->template, $this->params))
            ->run();
    }
}
