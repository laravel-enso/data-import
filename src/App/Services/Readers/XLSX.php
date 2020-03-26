<?php

namespace LaravelEnso\DataImport\App\Services\Readers;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\XLSX\Reader;
use Box\Spout\Reader\XLSX\RowIterator;
use Box\Spout\Reader\XLSX\Sheet;
use Box\Spout\Reader\XLSX\SheetIterator;
use Exception;
use Illuminate\Support\Str;
use LaravelEnso\DataImport\App\Exceptions\DataImport;

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
            $this->close();
        }
    }

    public function sheetIterator(): SheetIterator
    {
        $this->ensureIsOpen();

        $iterator = $this->reader->getSheetIterator();
        $iterator->rewind();

        return $iterator;
    }

    public function rowIteratorFor(string $sheetName, bool $header = false): RowIterator
    {
        return $this->rowIterator($this->sheet($sheetName), $header);
    }

    public function rowIterator(Sheet $sheet, bool $header = false): RowIterator
    {
        $iterator = $sheet->getRowIterator();
        $iterator->rewind();

        if (! $header) {
            $iterator->next();
        }

        return $iterator;
    }

    public function sheetName(Sheet $sheet): string
    {
        return Str::snake(Str::lower($sheet->getName()));
    }

    public function close(): void
    {
        $this->reader->close();

        $this->open = false;
    }

    private function sheet(string $sheetName): Sheet
    {
        $iterator = $this->sheetIterator();

        while ($iterator->valid()
            && $this->sheetName($iterator->current()) !== $sheetName) {
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
            throw DataImport::fileNotReadable();
        }

        $this->open = true;
    }
}
