<?php

namespace LaravelEnso\DataImport\Services\Importers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use LaravelEnso\DataImport\Contracts\AfterHook;
use LaravelEnso\DataImport\Contracts\Authenticates;
use LaravelEnso\DataImport\Contracts\BeforeHook;
use LaravelEnso\DataImport\Enums\Statuses;
use LaravelEnso\DataImport\Models\Chunk;
use LaravelEnso\DataImport\Models\Import;
use LaravelEnso\DataImport\Services\Exporters\Rejected;
use LaravelEnso\DataImport\Services\Importers\Chunk as ChunkImporter;
use LaravelEnso\DataImport\Services\Readers\XLSX;
use LaravelEnso\DataImport\Services\Sanitizers\Sanitize;
use LaravelEnso\DataImport\Services\Template;
use OpenSpout\Reader\XLSX\RowIterator;
use Throwable;

class SyncImport
{
    private XLSX $reader;

    private Template $template;

    public function __construct(private readonly Import $import)
    {
        $this->template = $this->import->template();
    }

    public function handle(): void
    {
        $this->import->update(['status' => Statuses::Processing]);

        try {
            $this->template->sheets()
                ->pluck('name')
                ->each(fn (string $sheet) => $this->sheet($sheet));

            $this->import->refresh();

            if ($this->import->failed > 0) {
                (new Rejected($this->import))->handle();
            }

            $this->import->update(['status' => Statuses::Finalized]);
        } catch (Throwable $throwable) {
            $this->import->fail();

            throw $throwable;
        }
    }

    private function sheet(string $sheet): void
    {
        $this->before($sheet);

        $iterator = $this->iterator($sheet);
        $header = Sanitize::header($iterator->current());
        $rowLength = $header->count();
        $iterator->next();

        while ($iterator->valid()) {
            $this->chunk($iterator, $sheet, $header->toArray(), $rowLength);
        }

        $this->after($sheet);
    }

    private function chunk(
        RowIterator $iterator,
        string $sheet,
        array $header,
        int $rowLength,
    ): void {
        $rows = [];

        while ($iterator->valid() && count($rows) < $this->template->chunkSize($sheet)) {
            $rows[] = Sanitize::cells($iterator->current()->getCells(), $rowLength);
            $iterator->next();
        }

        $chunk = $this->chunkContext($sheet, $header, $rows);

        (new ChunkImporter($chunk))->handle();
    }

    private function before(string $sheet): void
    {
        $importer = $this->template->importer($sheet);

        if ($importer instanceof BeforeHook) {
            $this->authenticate($importer);
            $importer->before($this->import);
        }
    }

    private function after(string $sheet): void
    {
        $importer = $this->template->importer($sheet);

        if ($importer instanceof AfterHook) {
            $this->authenticate($importer);
            $importer->after($this->import);
        }
    }

    private function authenticate(object $importer): void
    {
        if ($importer instanceof Authenticates) {
            Auth::setUser($this->import->createdBy);
        }
    }

    private function chunkContext(string $sheet, array $header, array $rows): Chunk
    {
        return $this->import->chunks()->make([
            'sheet' => $sheet,
            'header' => $header,
            'rows' => $rows,
        ])->setRelation('import', $this->import);
    }

    private function iterator(string $sheet): RowIterator
    {
        $file = Storage::path($this->import->file->path());
        $this->reader = new XLSX($file);

        return $this->reader->rowIterator($sheet);
    }
}
