<?php

namespace LaravelEnso\DataImport\app\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Services\Template;
use LaravelEnso\DataImport\app\Services\Exporters\Rejected;

class RejectedExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $dataImport;

    public $queue;
    public $timeout;

    public function __construct(DataImport $dataImport)
    {
        $this->dataImport = $dataImport;

        $this->queue = config('enso.imports.queues.rejected');
        $this->timeout = (new Template($dataImport))->timeout();
    }

    public function handle()
    {
        (new Rejected($this->dataImport))
            ->run();
    }
}
