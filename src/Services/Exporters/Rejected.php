<?php

namespace LaravelEnso\DataImport\Services\Exporters;

use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\XLSX\Writer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelEnso\Core\Models\User;
use LaravelEnso\DataImport\Enums\Statuses;
use LaravelEnso\DataImport\Models\DataImport;

class Rejected
{
    private DataImport $dataImport;
    private User $user;
    private Writer $writer;
    private Collection $sheets;
    private string $hashFilename;

    public function __construct(DataImport $dataImport, User $user)
    {
        $this->dataImport = $dataImport;
        $this->user = $user;
        $this->sheets = new Collection();
        $this->hashFilename = $this->hashFilename();
    }

    public function run(): void
    {
        $this->dumps()
            ->whenNotEmpty(fn ($dumps) => $this->process($dumps));
    }

    private function process(Collection $dumps): void
    {
        $this->exportStatus()
            ->initWriter();

        $dumps->each(fn ($file) => $this->export($file));

        $this->closeWriter()
            ->storeRejected();
    }

    private function exportStatus(): self
    {
        $this->dataImport->setStatus(Statuses::ExportingRejected);

        return $this;
    }

    private function initWriter(): void
    {
        $defaultStyle = (new StyleBuilder())
            ->setShouldWrapText(false)
            ->build();

        $this->writer = WriterEntityFactory::createXLSXWriter();

        $this->writer->setDefaultRowStyle($defaultStyle)
            ->openToFile(Storage::path($this->hashFilename));
    }

    private function export(string $file): void
    {
        $rejected = $this->content($file);

        $this->prepare($rejected);

        $rows = $rejected->map(fn ($row) => $this->row($row));

        $this->writer->addRows($rows->toArray());
    }

    private function prepare(Collection $rejected): void
    {
        $sheetName = $rejected->shift();
        $header = $rejected->shift();

        if ($this->sheets->contains($sheetName)) {
            return;
        }

        if ($this->sheets->isNotEmpty()) {
            $this->writer->addNewSheetAndMakeItCurrent();
        }

        $this->writer->getCurrentSheet()->setName($sheetName);
        $this->writer->addRow($this->row($header));

        $this->sheets->push($sheetName);
    }

    private function closeWriter(): self
    {
        $this->writer->close();
        unset($this->writer);

        return $this;
    }

    private function storeRejected(): self
    {
        $this->dataImport->rejected()
            ->create(['data_import_id' => $this->dataImport->id])
            ->attach($this->hashFilename, $this->filename(), $this->user);

        return $this;
    }

    private function content(string $file): Collection
    {
        return new Collection(json_decode(Storage::get($file), true));
    }

    private function dumps(): Collection
    {
        return new Collection(Storage::files(
            $this->dataImport->rejectedFolder()
        ));
    }

    private function filename(): string
    {
        [$baseName] = explode('.', $this->dataImport->file->original_name);

        return "{$baseName}_rejected.xlsx";
    }

    private function hashFilename(): string
    {
        $hash = Str::random(40);
        $path = $this->dataImport->rejectedFolder()
            .DIRECTORY_SEPARATOR
            ."{$hash}.xlsx";

        return $path;
    }

    private function row($row): Row
    {
        return WriterEntityFactory::createRowFromArray($row);
    }
}
