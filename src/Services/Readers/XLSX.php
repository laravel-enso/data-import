<?php

namespace LaravelEnso\DataImport\Services\Readers;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\XLSX\Reader;
use Box\Spout\Reader\XLSX\RowIterator;
use Box\Spout\Reader\XLSX\Sheet;
use Box\Spout\Reader\XLSX\SheetIterator;
use Exception;
use Illuminate\Support\Collection;
use LaravelEnso\DataImport\Exceptions\DataImport;
use LaravelEnso\DataImport\Services\Sanitizers\Sanitize;

class XLSX
{
    private string $file;
    private bool $open;
    private Reader $reader;

    public function __construct(string $file)
    {
        $this->file = $file;
        $this->open = false;
        $this->reader = ReaderEntityFactory::createXLSXReader();
    }

    public function __destruct()
    {
        if ($this->open) {
            $this->reader->close();
            $this->open = false;
        }
    }

    public function sheets(): Collection
    {
        $iterator = $this->sheetIterator();
        $sheets = new Collection();

        while ($iterator->valid()) {
            $sheets->push($iterator->current()->getName());
            $iterator->next();
        }

        return Sanitize::sheets($sheets);
    }

    public function sheetIterator(): SheetIterator
    {
        $this->ensureIsOpen();

        $iterator = $this->reader->getSheetIterator();
        $iterator->rewind();

        return $iterator;
    }

    public function rowIterator(string $sheet): RowIterator
    {
        $iterator = $this->sheet($sheet)->getRowIterator();
        $iterator->rewind();

        return $iterator;
    }

    private function sheet(string $name): ?Sheet
    {
        $iterator = $this->sheetIterator();

        while (Sanitize::name($iterator->current()->getName()) !== $name) {
            $iterator->next();
        }

        return $iterator->current();
    }

    private function ensureIsOpen(): void
    {
        if (! $this->open) {
            $this->open();
        }
    }

    private function open(): void
    {
        try {
            $this->reader->open($this->file);
        } catch (Exception $exception) {
            throw DataImport::fileNotReadable($this->file);
        }

        $this->open = true;
    }
}
