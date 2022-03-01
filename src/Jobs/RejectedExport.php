<?php

namespace LaravelEnso\DataImport\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use LaravelEnso\DataImport\Models\Import;
use LaravelEnso\DataImport\Services\Exporters\Rejected;
use LaravelEnso\DataImport\Services\Template;

class RejectedExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout;

    public function __construct(private Import $import)
    {
        $this->queue = Config::get('enso.imports.queues.rejected');
        $this->timeout = (new Template($import->type))->timeout();
    }

    public function handle()
    {
        if ($this->import->failed > 0) {
            (new Rejected($this->import))->handle();
        }
    }
}
