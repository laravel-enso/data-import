<?php

namespace LaravelEnso\DataImport\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use LaravelEnso\DataImport\Models\Import as Model;
use LaravelEnso\DataImport\Services\Importers\Import as Service;

class Import implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout;

    public function __construct(
        private Model $import,
        private string $sheet
    ) {
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
