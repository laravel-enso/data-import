<?php

namespace LaravelEnso\DataImport\app\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Collection;
use LaravelEnso\Core\app\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use LaravelEnso\Helpers\app\Classes\Obj;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Services\Template;
use LaravelEnso\DataImport\app\Services\Importers\Chunk;

class ChunkImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $dataImport;
    private $template;
    private $user;
    private $params;
    private $sheetName;
    private $chunk;

    public $queue;

    public function __construct(DataImport $dataImport, Template $template, User $user, Obj $params, string $sheetName, Collection $chunk)
    {
        $this->dataImport = $dataImport;
        $this->template = $template;
        $this->user = $user;
        $this->params = $params;
        $this->sheetName = $sheetName;
        $this->chunk = $chunk;

        $this->queue = $template->queue();
    }

    public function handle()
    {
        (new Chunk(
            $this->dataImport,
            $this->template,
            $this->user,
            $this->params,
            $this->sheetName,
            $this->chunk
        ))->run();
    }
}
