<?php

namespace LaravelEnso\DataImport\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use LaravelEnso\DataImport\Contracts\BeforeHook;
use LaravelEnso\DataImport\Enums\Statuses;
use LaravelEnso\DataImport\Models\DataImport;
use LaravelEnso\DataImport\Notifications\ImportDone;

class Finalize implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $queue;

    private DataImport $import;

    public function __construct(DataImport $import)
    {
        $this->import = $import;

        $this->queue = Config::get('enso.imports.queues.processing');
    }

    public function handle()
    {
        $this->import->update(['status' => Statuses::Finalized]);

        $this->notify();
    }

    private function after(string $sheet)
    {
        $importer = $this->import->template()->importer($sheet);

        if ($importer instanceof BeforeHook) {
            $importer->before(
                $this->import->createdBy,
                $this->import->params
            );
        }

        return $this;
    }

    private function notify()
    {
        $this->import->file->createdBy->notify(
            (new ImportDone($this->import))
                ->onQueue(config('enso.imports.queues.notifications'))
        );
    }
}
