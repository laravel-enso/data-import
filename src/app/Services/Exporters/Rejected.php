<?php

namespace LaravelEnso\DataImport\app\Services\Exporters;

use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Http\File;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelEnso\Core\app\Models\User;
use LaravelEnso\DataImport\app\Enums\Statuses;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Notifications\ImportDone;

class Rejected
{
    private $dataImport;
    private $user;
    private $writer;
    private $path;
    private $filename;
    private $sheets;
    private $dumps;

    public function __construct(DataImport $dataImport, User $user)
    {
        $this->dataImport = $dataImport;
        $this->user = $user;
        $this->sheets = collect();
        $this->dumps = $this->dumps();
    }

    public function run()
    {
        if ($this->dumps->isNotEmpty()) {
            $this->start()
                ->initWriter();

            $this->dumps->each(fn($file) => $this->export($this->content($file)));

            $this->closeWriter()
                ->storeRejected();
        }

        $this->finalize()
            ->notify();
    }

    private function start()
    {
        $this->dataImport->setStatus(Statuses::ExportingRejected);

        return $this;
    }

    private function export(Collection $rejected)
    {
        $this->prepare($rejected);

        $rows = $rejected->map(fn($row) => $this->row($row));

        $this->writer->addRows($rows->toArray());
    }

    private function prepare(Collection $rejected)
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

    private function initWriter()
    {
        $defaultStyle = (new StyleBuilder())
            ->setShouldWrapText(false)
            ->build();

        $this->writer = WriterEntityFactory::createXLSXWriter();

        $this->writer->setDefaultRowStyle($defaultStyle)
            ->openToFile(Storage::path($this->path()));
    }

    private function closeWriter()
    {
        $this->writer->close();
        unset($this->writer);

        return $this;
    }

    private function storeRejected()
    {
        $this->dataImport->rejected()
            ->create(['data_import_id' => $this->dataImport->id])
            ->attach(
                new File(Storage::path($this->path())), $this->filename(), $this->user
            );

        return $this;
    }

    private function finalize()
    {
        $this->dataImport->endOperation();

        return $this;
    }

    private function notify()
    {
        optional($this->user())->notify(
            (new ImportDone($this->dataImport->fresh()))
                ->onQueue(config('enso.imports.queues.notifications'))
        );
    }

    private function content(string $file)
    {
        return collect(json_decode(Storage::get($file), true));
    }

    private function dumps()
    {
        return collect(Storage::files(
            $this->dataImport->rejectedFolder()
        ));
    }

    private function path()
    {
        return $this->path ??= $this->dataImport->rejectedFolder()
            .DIRECTORY_SEPARATOR
            .$this->hashName();
    }

    private function filename()
    {
        return $this->filename ??= 
            tap(collect(explode('.', $this->dataImport->file->original_name)))
                ->pop()
                ->implode('.').'_rejected.xlsx';
    }

    private function hashName()
    {
        return Str::random(40).'.xlsx';
    }

    private function user()
    {
        return optional($this->dataImport->file)->createdBy;
    }

    private function row($row)
    {
        return WriterEntityFactory::createRowFromArray($row);
    }
}
