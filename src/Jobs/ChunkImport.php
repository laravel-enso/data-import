<?php

namespace LaravelEnso\DataImport\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use LaravelEnso\Core\Models\User;
use LaravelEnso\DataImport\Enums\Statuses;
use LaravelEnso\DataImport\Models\DataImport;
use LaravelEnso\DataImport\Services\Importers\Chunk;
use LaravelEnso\DataImport\Services\Template;
use LaravelEnso\Helpers\Services\Obj;

class ChunkImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $queue;

    private DataImport $dataImport;
    private Template $template;
    private User $user;
    private Obj $params;
    private string $sheetName;
    private Collection $chunk;
    private int $index;

    public function __construct(DataImport $dataImport, Template $template, User $user, Obj $params, string $sheetName, Collection $chunk)
    {
        $this->dataImport = $dataImport;
        $this->template = $template;
        $this->user = $user;
        $this->params = $params;
        $this->sheetName = $sheetName;
        $this->chunk = $chunk;
        $this->index = $dataImport->chunks;

        $this->queue = $template->queue();
    }

    public function handle()
    {
        if ($this->dataImport->status === Statuses::Canceled) {
            return;
        }

        (new Chunk(
            $this->dataImport,
            $this->template,
            $this->user,
            $this->params,
            $this->sheetName,
            $this->chunk,
            $this->index
        ))->run();
    }
}
