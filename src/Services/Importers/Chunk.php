<?php

namespace LaravelEnso\DataImport\Services\Importers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LaravelEnso\Core\Models\User;
use LaravelEnso\DataImport\Contracts\Authenticates;
use LaravelEnso\DataImport\Contracts\Authorizes;
use LaravelEnso\DataImport\Contracts\Importable;
use LaravelEnso\DataImport\Exceptions\DataImport as Exception;
use LaravelEnso\DataImport\Models\Chunk as Model;
use LaravelEnso\DataImport\Models\DataImport;
use LaravelEnso\DataImport\Models\RejectedChunk;
use LaravelEnso\DataImport\Services\Validators\Row;
use LaravelEnso\Helpers\Services\Obj;
use Throwable;

class Chunk
{
    private DataImport $import;
    private User $user;
    private Model $chunk;
    private RejectedChunk $rejectedChunk;
    private Importable $importer;

    public function __construct(Model $chunk)
    {
        $this->chunk = $chunk;
        $this->import = $chunk->import;
        $this->user = $chunk->import->createdBy;
        $this->importer = $chunk->importer();
        $this->rejectedChunk = $this->rejectedChunk();
    }

    public function handle(): void
    {
        $this->authorize()
            ->authenticate();

        Collection::wrap($this->chunk->rows)
            ->each(fn ($row) => $this->process($row));

        $this->dumpRejected()
            ->updateProgress();

        $this->chunk->delete();
    }

    private function authorize(): self
    {
        $unauthorized = $this->importer instanceof Authorizes
            && ! $this->importer->authorizes($this->user, $this->import->params);

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

    private function process(array $row): void
    {
        $rowObj = $this->row($row);
        $validator = new Row($rowObj, $this->chunk);

        if ($validator->passes()) {
            $this->import($rowObj);
        } else {
            $row[] = $validator->errors()->implode(' | ');
            $this->rejectedChunk->add($row);
        }
    }

    private function row(array $row): Obj
    {
        return new Obj(array_combine($this->chunk->header, $row));
    }

    private function import(Obj $row): void
    {
        try {
            $params = new Obj($this->import->params);
            $this->importer->run($row, $this->user, $params);
        } catch (Throwable $exception) {
            $row = $row->values()->toArray();
            $row[] = Config::get('enso.imports.unknownError');
            $this->rejectedChunk->rows->push($row);
            Log::debug($exception->getMessage());
        }
    }

    private function dumpRejected(): self
    {
        if (! $this->rejectedChunk->empty()) {
            $this->rejectedChunk->save();
        }

        return $this;
    }

    private function updateProgress(): void
    {
        $total = $this->chunk->count();
        $failed = $this->rejectedChunk->count();

        DB::transaction(fn () => DataImport::lockForUpdate()
            ->whereId($this->import->id)->first()
            ->updateProgress($total - $failed, $failed));
    }

    private function rejectedChunk(): RejectedChunk
    {
        return RejectedChunk::factory()->make([
            'import_id' => $this->import->id,
            'sheet' => $this->chunk->sheet,
            'header' => $this->chunk->header,
        ]);
    }
}
