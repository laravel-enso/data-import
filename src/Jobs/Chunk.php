<?php

namespace LaravelEnso\DataImport\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use LaravelEnso\DataImport\Models\DataImport;
use LaravelEnso\DataImport\Services\DTOs\Chunk as DTO;
use LaravelEnso\DataImport\Services\Importers\Chunk as Service;

class Chunk implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private DataImport $import;
    private DTO $chunk;
    private int $index;

    public function __construct(DataImport $import, DTO $chunk)
    {
        $this->import = $import;
        $this->chunk = $chunk;
    }

    public function handle()
    {
        if ($this->import->processing()) {
            (new Service($this->import, $this->chunk))->handle();
        }
    }
}
