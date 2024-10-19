<?php

namespace LaravelEnso\DataImport\Services\Readers;

use Exception;
use LaravelEnso\DataImport\Exceptions\Import;
use OpenSpout\Reader\AbstractReader;
use OpenSpout\Reader\SheetIteratorInterface;

abstract class Reader
{
    protected bool $open;
    protected AbstractReader $reader;

    public function __construct(protected string $file)
    {
        $this->open = false;
    }

    public function __destruct()
    {
        if ($this->open) {
            $this->reader->close();
            $this->open = false;
        }
    }

    public function sheetIterator(): SheetIteratorInterface
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
