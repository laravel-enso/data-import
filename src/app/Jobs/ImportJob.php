<?php

namespace LaravelEnso\DataImport\app\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use LaravelEnso\DataImport\app\Classes\Template;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Classes\Importers\Import;

class ImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $import;
    private $template;

    public function __construct(DataImport $import, Template $template)
    {
        $this->import = $import;
        $this->template = $template;
        $this->timeout = config('enso.imports.timeout');
    }

    public function handle()
    {
        (new Import(
            $this->import, $this->template
        ))->run();
    }
}
