<?php

namespace LaravelEnso\DataImport\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use LaravelEnso\DataImport\Enums\Status;
use LaravelEnso\DataImport\Models\Import;
use LaravelEnso\DataImport\Notifications\ImportDone;
use LaravelEnso\DataImport\Services\Notifiables;

class Finalize implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Import $import)
    {
        $this->queue = Config::get('enso.imports.queues.processing');
    }

    public function handle()
    {
        $this->import->update(['status' => Status::Finalized]);

        $this->notify();
    }

    private function notify(): void
    {
        $queue = Config::get('enso.imports.queues.notifications');
        $notification = (new ImportDone($this->import))->onQueue($queue);

        $this->import->file->createdBy->notify($notification);

        if ($this->import->template()->notifies()) {
            Notifiables::get($this->import)->each->notify($notification);
        }
    }
}
