<?php

namespace LaravelEnso\DataImport\Services\Readers;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\CSV\Reader;
use Box\Spout\Reader\CSV\RowIterator;
use Box\Spout\Reader\CSV\Sheet;
use Box\Spout\Reader\CSV\SheetIterator;
use Exception;
use LaravelEnso\DataImport\Exceptions\Import;

class CSV
{
    private bool $open;
    private Reader $reader;

    public function __construct(private string $file)
    {
        $this->open = false;
        $this->reader = ReaderEntityFactory::createCSVReader();
    }

    public function __destruct()
    {
        if ($this->open) {
            $this->reader->close();
            $this->open = false;
        }
    }

    public function rowIterator(string $sheet): RowIterator
    {
        $iterator = $this->sheet()->getRowIterator();
        $iterator->rewind();

        return $iterator;
    }

    private function sheet(): ?Sheet
    {
        $iterator = $this->sheetIterator();

        return $iterator->current();
    }

    public function sheetIterator(): SheetIterator
    {
        $this->ensureIsOpen();

        $iterator = $this->reader->getSheetIterator();
        $iterator->rewind();

        return $iterator;
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
        } catch (Exception) {
            throw Import::fileNotReadable($this->file);
        }

        $this->open = true;
    }
}
