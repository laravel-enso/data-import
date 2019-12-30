<?php

namespace LaravelEnso\DataImport\App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use LaravelEnso\DataImport\App\Models\DataImport;
use LaravelEnso\DataImport\App\Services\DTOs\Sheets;
use LaravelEnso\DataImport\App\Services\Importers\Import as Service;
use LaravelEnso\DataImport\App\Services\Template;
use LaravelEnso\Helpers\App\Classes\Obj;

class Import implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $queue;
    public $timeout;

    private DataImport $dataImport;
    private Template $template;
    private Sheets $sheets;
    private Obj $params;
    private $user;

    public function __construct(DataImport $dataImport, Template $template, Sheets $sheets,
        Obj $params)
    {
        $this->dataImport = $dataImport;
        $this->template = $template;
        $this->user = Auth::user();
        $this->sheets = $sheets;
        $this->params = $params;

        $this->queue = config('enso.imports.queues.splitting');
        $this->timeout = $template->timeout();
    }

    public function handle()
    {
        (new Service(
            $this->dataImport, $this->template, $this->sheets, $this->user, $this->params
        ))->run();
    }
}
