<?php

namespace LaravelEnso\DataImport\Services\Exporters;

use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\XLSX\Writer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelEnso\DataImport\Enums\Status;
use LaravelEnso\DataImport\Models\Import;
use LaravelEnso\DataImport\Models\RejectedChunk;
use LaravelEnso\DataImport\Models\RejectedImport;
use LaravelEnso\Files\Models\File;
use LaravelEnso\Files\Models\Type;

class Rejected
{
    private RejectedImport $rejected;
    private string $savedName;
    private bool $firstChunk;
    private Writer $xlsx;

    public function __construct(private Import $import)
    {
        $this->rejected = $this->import->rejected()->make();
        $this->savedName = $this->savedName();
        $this->firstChunk = true;
    }

    public function handle(): void
    {
        $this->import->update(['status' => Status::ExportingRejected]);

        $this->initWriter();

        $this->import->rejectedChunks->sortBy('sheet')
            ->each(fn ($chunk) => $this->export($chunk));

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

        $path = Type::for($this->rejected::class)->path($this->savedName);

        $this->xlsx->setDefaultRowStyle($defaultStyle)
            ->openToFile(Storage::path($path));
    }

    private function export(RejectedChunk $chunk): void
    {
        $this->prepare($chunk);

        Collection::wrap($chunk->rows)
            ->each(fn ($row) => $this->xlsx->addRow($this->row($row)));
    }

    private function prepare(RejectedChunk $chunk): void
    {
        if ($this->firstChunk) {
            $this->firstChunk = false;
            $this->initSheet($chunk);
        } elseif ($this->needsNewSheet($chunk->sheet)) {
            $this->xlsx->addNewSheetAndMakeItCurrent();
            $this->initSheet($chunk);
        }
    }

    private function initSheet(RejectedChunk $chunk): void
    {
        $this->xlsx->getCurrentSheet()->setName($chunk->sheet);
        $this->addHeader($chunk);
    }

    private function addHeader(RejectedChunk $chunk)
    {
        $header = $chunk->header;
        $header[] = Config::get('enso.imports.errorColumn');
        $this->xlsx->addRow($this->row($header));
    }

    private function closeWriter(): self
    {
        $this->xlsx->close();
        unset($this->xlsx);

        return $this;
    }

    private function storeRejected(): self
    {
        $args = [
            $this->rejected, $this->savedName,
            $this->filename(), $this->import->created_by,
        ];

        $file = File::attach(...$args);

        $this->rejected->file()->associate($file)->save();

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

    private function savedName(): string
    {
        $hash = Str::random(40);

        return "{$hash}.xlsx";
    }

    private function row(array $row): Row
    {
        return WriterEntityFactory::createRowFromArray($row);
    }

    private function needsNewSheet(string $sheet): bool
    {
        return $this->xlsx->getCurrentSheet()->getName() !== $sheet;
    }
}
