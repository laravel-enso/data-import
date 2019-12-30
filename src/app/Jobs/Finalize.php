<?php

namespace LaravelEnso\DataImport\App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use LaravelEnso\DataImport\App\Models\DataImport;
use LaravelEnso\DataImport\App\Notifications\ImportDone;

class Finalize implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $queue;

    private DataImport $dataImport;

    public function __construct(DataImport $dataImport)
    {
        $this->dataImport = $dataImport;

        $this->queue = config('enso.imports.queues.rejected');
    }

    public function handle()
    {
        $this->dataImport->refresh()
            ->endOperation();

        $this->notify();
    }

    private function notify()
    {
        $this->dataImport->file->createdBy->notify(
            (new ImportDone($this->dataImport))
                ->onQueue(config('enso.imports.queues.notifications'))
        );
    }
}
