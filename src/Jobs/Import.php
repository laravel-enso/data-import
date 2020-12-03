<?php

namespace LaravelEnso\DataImport\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use LaravelEnso\DataImport\Models\DataImport;
use LaravelEnso\DataImport\Services\Importers\Import as Service;

class Import implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $queue;
    public $timeout;

    private DataImport $import;

    private string $sheet;

    public function __construct(DataImport $import, string $sheet)
    {
        $this->import = $import;
        $this->sheet = $sheet;

        $this->queue = Config::get('enso.imports.queues.splitting');
        $this->timeout = $this->import->template()->timeout();
    }

    public function handle()
    {
        if (! $this->import->cancelled()) {
            (new Service($this->import, $this->sheet))->handle();
        }
    }
}
