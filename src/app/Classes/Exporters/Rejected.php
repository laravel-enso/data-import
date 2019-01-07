<?php

namespace LaravelEnso\DataImport\app\Classes\Exporters;

use Box\Spout\Common\Type;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Writer\Style\StyleBuilder;
use LaravelEnso\DataImport\app\Enums\Statuses;
use LaravelEnso\DataImport\app\Models\DataImport;

class Rejected
{
    private $import;
    private $header;
    private $writer;
    private $filename;
    private $sheets;
    private $dumps;

    public function __construct(DataImport $import)
    {
        $this->import = $import;
        $this->filename = $this->filename();
        $this->sheets = collect();
        $this->dumps = $this->dumps();
    }

    public function run()
    {
        $this->init();

        $this->dumps->each(function ($file) {
            $this->export($this->content($file));
        });

        $this->store();
    }

    private function init()
    {
        $this->import->update(['status' => Statuses::ExportingRejected]);
        // sleep(5);
        $this->setWriter();
    }

    private function export(Collection $rejected)
    {
        $this->prepareWorkingSheet($rejected);
        $this->writer->addRows($rejected->toArray());
    }

    private function prepareWorkingSheet(Collection $rejected)
    {
        $sheetName = $rejected->shift();
        $header = $rejected->shift();

        if ($this->sheets->contains($sheetName)) {
            return;
        }

        if ($this->sheets->isNotEmpty()) {
            $this->writer->addNewSheetAndMakeItCurrent();
        }

        $this->sheets->push($sheetName);
        $this->setSheetName($sheetName);
        $this->writer->addRow($header);
    }

    private function setSheetName($sheetName)
    {
        $this->writer->getCurrentSheet()
            ->setName($sheetName);
    }

    private function setWriter()
    {
        $defaultStyle = (new StyleBuilder())
            ->setShouldWrapText(false)
            ->build();

        $this->writer = WriterFactory::create(Type::XLSX);

        $this->writer->setDefaultRowStyle($defaultStyle)
            ->openToFile(\Storage::path($this->path()));
    }

    private function store()
    {
        $this->writer->close();
        unset($this->writer);

        $this->import->rejected()
            ->create(['data_import_id' => $this->import->id])
            ->upload($this->fileWrapper());

        $this->import->update(['status' => Statuses::Finalized]);
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
            $this->import->rejectedFolder()
        ));
    }

    private function path()
    {
        return $this->import->rejectedFolder()
            .DIRECTORY_SEPARATOR
            .$this->filename;
    }

    private function filename()
    {
        return tap(collect(explode('.', $this->import->file->original_name)))
            ->pop()
            ->implode('.').'_rejected.xlsx';
    }
}
