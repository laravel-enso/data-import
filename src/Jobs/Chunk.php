<?php

namespace LaravelEnso\DataImport\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use LaravelEnso\DataImport\Models\Chunk as Model;
use LaravelEnso\DataImport\Services\Importers\Chunk as Service;

class Chunk implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Model $chunk;

    public function __construct(Model $chunk)
    {
        $this->chunk = $chunk;
    }

    public function handle()
    {
        if (!$this->batch()->cancelled()) {
            (new Service($this->chunk))->handle();
        }
    }
}
