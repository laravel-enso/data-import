<?php

namespace LaravelEnso\DataImport\app\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use LaravelEnso\DataImport\app\Classes\Template;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Classes\Importers\Chunk;

class ChunkImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $import;
    private $template;
    private $sheetName;
    private $chunk;
    private $index;
    private $isLast;

    public function __construct(DataImport $import, Template $template, string $sheetName, Collection $chunk, int $index, bool $isLast)
    {
        $this->import = $import;
        $this->template = $template;
        $this->sheetName = $sheetName;
        $this->chunk = $chunk;
        $this->index = $index;
        $this->isLast = $isLast;
        $this->queue = config('enso.imports.queues.processing');
    }

    public function handle()
    {
        (new Chunk(
            $this->import,
            $this->template,
            $this->sheetName,
            $this->chunk,
            $this->index,
            $this->isLast
        ))->run();
    }
}
