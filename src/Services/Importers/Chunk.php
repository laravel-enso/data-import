<?php

namespace LaravelEnso\DataImport\Services\Importers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LaravelEnso\Core\Models\User;
use LaravelEnso\DataImport\Contracts\Authenticates;
use LaravelEnso\DataImport\Contracts\Authorizes;
use LaravelEnso\DataImport\Contracts\Importable;
use LaravelEnso\DataImport\Exceptions\DataImport as Exception;
use LaravelEnso\DataImport\Models\DataImport;
use LaravelEnso\DataImport\Services\DTOs\Chunk as DTO;
use LaravelEnso\DataImport\Services\DTOs\Row;
use LaravelEnso\DataImport\Services\Template;
use LaravelEnso\DataImport\Services\Validators\Row as Validator;
use LaravelEnso\Helpers\Services\Obj;
use Throwable;

class Chunk
{
    private DataImport $import;
    private Template $template;
    private User $user;
    private DTO $chunk;
    private Importable $importer;

    public function __construct(DataImport $import, DTO $chunk)
    {
        $this->import = $import;
        $this->template = $import->template();
        $this->user = $import->createdBy;
        $this->chunk = $chunk;
        $this->importer = $this->template->importer($chunk->sheet());
    }

    public function handle(): void
    {
        $this->authorize()
            ->authenticate();

        $this->chunk->each(fn ($row) => $this->process($row));

        $this->dumpRejected()
            ->updateProgress();
    }

    private function authorize(): self
    {
        $unauthorized = $this->importer instanceof Authorizes && ! $this->importer
            ->authorizes($this->import->createdBy, $this->import->params);

        if ($unauthorized) {
            throw Exception::unauthorized();
        }

        return $this;
    }

    private function authenticate(): void
    {
        if ($this->importer instanceof Authenticates) {
            Auth::setUser($this->user);
        }
    }

    private function process(Row $row): void
    {
        Validator::run($row, $this->import, $this->chunk->sheet());

        if ($row->valid()) {
            $this->import($row);
        } else {
            $this->chunk->reject($row);
        }
    }

    private function import(Row $row): void
    {
        try {
            $this->importer->run(
                $row->content(),
                $this->import->createdBy,
                new Obj($this->import->params)
            );
        } catch (Throwable $exception) {
            $row->unknownError();
            $this->chunk->reject($row);
            Log::debug($exception->getMessage());
        }
    }

    private function dumpRejected(): self
    {
        if (! $this->chunk->rejected()->empty()) {
            $this->import->rejectedChunks()
                ->save($this->chunk->rejected());
        }

        return $this;
    }

    private function updateProgress(): void
    {
        DB::transaction(fn () => DataImport::query()
            ->whereId($this->import->id)
            ->lockForUpdate()->first()
            ->updateProgress($this->chunk));
    }
}
