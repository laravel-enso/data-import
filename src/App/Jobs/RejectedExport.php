<?php

namespace LaravelEnso\DataImport\App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use LaravelEnso\Core\App\Models\User;
use LaravelEnso\DataImport\App\Models\DataImport;
use LaravelEnso\DataImport\App\Services\Exporters\Rejected;
use LaravelEnso\DataImport\App\Services\Template;

class RejectedExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $queue;
    public $timeout;

    private $dataImport;
    private $user;

    public function __construct(DataImport $dataImport, User $user)
    {
        $this->dataImport = $dataImport;
        $this->user = $user;

        $this->queue = config('enso.imports.queues.rejected');
        $this->timeout = (new Template($dataImport))->timeout();
    }

    public function handle()
    {
        (new Rejected($this->dataImport, $this->user))->run();
    }
}
