<?php

namespace LaravelEnso\DataImport\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use LaravelEnso\DataImport\Contracts\AfterHook;
use LaravelEnso\DataImport\Enums\Statuses;
use LaravelEnso\DataImport\Models\DataImport;
use LaravelEnso\DataImport\Notifications\ImportDone;

class Finalize implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private DataImport $import)
    {
        $this->queue = Config::get('enso.imports.queues.processing');
    }

    public function handle()
    {
        $this->import->update(['status' => Statuses::Finalized]);

        $this->notify();
    }

    private function after(string $sheet): self
    {
        $importer = $this->import->template()->importer($sheet);

        if ($importer instanceof AfterHook) {
            $importer->after($this->import);
        }

        return $this;
    }

    private function notify(): void
    {
        $queue = Config::get('enso.imports.queues.notifications');
        $notification = (new ImportDone($this->import))->onQueue($queue);
        $this->import->file->createdBy->notify($notification);
    }
}
