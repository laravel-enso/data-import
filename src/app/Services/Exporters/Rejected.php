<?php

namespace LaravelEnso\DataImport\app\Services\Exporters;

use Illuminate\Http\File;
use Box\Spout\Common\Type;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Box\Spout\Writer\WriterFactory;
use Illuminate\Support\Facades\Storage;
use Box\Spout\Writer\Style\StyleBuilder;
use LaravelEnso\DataImport\app\Enums\Statuses;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Notifications\ImportDone;

class Rejected
{
    private $dataImport;
    private $writer;
    private $path;
    private $filename;
    private $sheets;
    private $dumps;

    public function __construct(DataImport $dataImport)
    {
        $this->dataImport = $dataImport;
        $this->sheets = collect();
        $this->dumps = $this->dumps();
    }

    public function run()
    {
        if ($this->dumps->isNotEmpty()) {
            $this->start()
                ->initWriter();

            $this->dumps->each(function ($file) {
                $this->export($this->content($file));
            });

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

        $this->writer->addRows($rejected->toArray());
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
        $this->writer->addRow($header);

        $this->sheets->push($sheetName);
    }

    private function initWriter()
    {
        $defaultStyle = (new StyleBuilder())
            ->setShouldWrapText(false)
            ->build();

        $this->writer = WriterFactory::create(Type::XLSX);

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
                new File(Storage::path($this->path())), $this->filename()
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
        return $this->path
            ?? $this->path = $this->dataImport->rejectedFolder()
                .DIRECTORY_SEPARATOR
                .$this->hashName();
    }

    private function filename()
    {
        return $this->filename
            ?? $this->filename = tap(
                    collect(explode('.', $this->dataImport->file->original_name))
                )->pop()
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
}
