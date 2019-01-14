<?php

namespace LaravelEnso\DataImport\app\Classes\Exporters;

use Box\Spout\Common\Type;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Writer\Style\StyleBuilder;
use LaravelEnso\DataImport\app\Enums\Statuses;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Notifications\ImportDone;

class Rejected
{
    private $dataImport;
    private $writer;
    private $filename;
    private $sheets;
    private $dumps;

    public function __construct(DataImport $dataImport)
    {
        $this->dataImport = $dataImport;
        $this->filename = $this->filename();
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
                ->storeRejected()
                ->cleanUp();
        }

        $this->finalize()
            ->notify();
    }

    private function start()
    {
        $this->dataImport->update(['status' => Statuses::ExportingRejected]);

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
            ->openToFile(\Storage::path($this->path()));
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
            ->upload($this->fileWrapper());

        return $this;
    }

    private function cleanUp()
    {
        \Storage::delete($this->path());
    }

    private function finalize()
    {
        $this->dataImport->update(['status' => Statuses::Finalized]);

        return $this;
    }

    private function notify()
    {
        optional($this->user())->notify(
            (new ImportDone($this->dataImport))
                ->onQueue(config('enso.imports.queues.notifications'))
        );
    }

    private function content(string $file)
    {
        return collect(json_decode(\Storage::get($file), true));
    }

    private function fileWrapper()
    {
        return new UploadedFile(
            storage_path('app'.DIRECTORY_SEPARATOR.$this->path()),
            $this->filename,
            \Storage::mimeType($this->path()),
            \Storage::size($this->path()),
            0,
            true
        );
    }

    private function dumps()
    {
        return collect(\Storage::files(
            $this->dataImport->rejectedFolder()
        ));
    }

    private function path()
    {
        return $this->dataImport->rejectedFolder()
            .DIRECTORY_SEPARATOR
            .$this->filename;
    }

    private function filename()
    {
        return tap(collect(explode('.', $this->dataImport->file->original_name)))
            ->pop()
            ->implode('.').'_rejected.xlsx';
    }

    private function user()
    {
        return optional($this->dataImport->file)->createdBy;
    }
}
