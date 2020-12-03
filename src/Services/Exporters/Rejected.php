<?php

namespace LaravelEnso\DataImport\Services\Exporters;

use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\XLSX\Writer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelEnso\DataImport\Enums\Statuses;
use LaravelEnso\DataImport\Models\DataImport;
use LaravelEnso\DataImport\Models\RejectedChunk;
use LaravelEnso\DataImport\Models\RejectedImport;

class Rejected
{
    private DataImport $import;
    private RejectedImport $rejected;
    private Writer $xlsx;
    private Collection $sheets;
    private string $path;
    private bool $firstChunk;

    public function __construct(DataImport $import)
    {
        $this->import = $import;
        $this->rejected = $this->import->rejected()->make();
        $this->sheets = new Collection();
        $this->path = $this->path();
        $this->firstChunk = true;
    }

    public function handle(): void
    {
        $this->import->setStatus(Statuses::ExportingRejected);

        $this->initWriter();

        $this->import->rejectedChunks
            ->unlessEmpty(fn ($chunks) => $chunks->sortBy('sheet')
                ->each(fn ($chunk) => $this->export($chunk)));

        $this->closeWriter()
            ->storeRejected()
            ->cleanUp();
    }

    private function initWriter(): void
    {
        $defaultStyle = (new StyleBuilder())
            ->setShouldWrapText(false)
            ->build();

        $this->xlsx = WriterEntityFactory::createXLSXWriter();

        $this->xlsx->setDefaultRowStyle($defaultStyle)
            ->openToFile(Storage::path($this->path));
    }

    private function export(RejectedChunk $chunk): void
    {
        $this->prepare($chunk);

        Collection::wrap($chunk->content)
            ->each(fn ($row) => $this->xlsx->addRow($this->row($row)));
    }

    private function prepare(RejectedChunk $chunk): void
    {
        if ($this->needsNewSheet($chunk)) {
            $this->xlsx->addNewSheetAndMakeItCurrent();
        }

        $this->xlsx->getCurrentSheet()->setName($chunk->sheet);

        $this->xlsx->addRow($this->row($chunk->header));

        $this->sheets->push($chunk->sheet);
    }

    private function closeWriter(): self
    {
        $this->xlsx->close();
        unset($this->xlsx);

        return $this;
    }

    private function storeRejected(): self
    {
        tap($this->rejected)->save()
            ->file->attach($this->path, $this->filename(), $this->import->createdBy);

        return $this;
    }

    private function cleanUp(): void
    {
        $this->import->rejectedChunks()->delete();
    }

    private function filename(): string
    {
        [$baseName] = explode('.', $this->import->file->original_name);

        return "{$baseName}_rejected.xlsx";
    }

    private function path(): string
    {
        $hash = Str::random(40);

        return "{$this->rejected->folder()}/{$hash}.xlsx";
    }

    private function row($row): Row
    {
        return WriterEntityFactory::createRowFromArray($row);
    }

    private function needsNewSheet(RejectedChunk $chunk): bool
    {
        if ($this->firstChunk) {
            return $this->firstChunk = false;
        }

        return $this->xlsx->getCurrentSheet()
            ->getName() !== $chunk->sheet;
    }
}
