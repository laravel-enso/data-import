<?php

namespace LaravelEnso\DataImport\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use LaravelEnso\DataImport\Models\Import;
use LaravelEnso\DataImport\Services\Importers\Sheet as Service;

class Sheet implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Import $import,
        private string $sheet
    ) {
    }

    public function handle()
    {
        if (! $this->batch()->cancelled()) {
            (new Service($this->batch(), $this->import, $this->sheet))->handle();
        }
    }
}
