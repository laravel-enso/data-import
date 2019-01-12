<?php

namespace LaravelEnso\DataImport\app\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use LaravelEnso\DataImport\app\Classes\Template;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Classes\Exporters\Rejected;

class RejectedExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $dataImport;

    public function __construct(DataImport $dataImport)
    {
        $this->dataImport = $dataImport;
        $this->timeout = (new Template($dataImport))->timeout();
        $this->queue = config('enso.imports.queues.rejected');
    }

    public function handle()
    {
        (new Rejected($this->dataImport))
            ->run();
    }
}
