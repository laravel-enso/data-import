<?php

namespace LaravelEnso\DataImport\Services\Readers;

use Box\Spout\Reader\IteratorInterface;
use Exception;
use LaravelEnso\DataImport\Exceptions\Import;

abstract class Reader
{
    public function __destruct()
    {
        if ($this->open) {
            $this->reader->close();
            $this->open = false;
        }
    }

    public function sheetIterator(): IteratorInterface
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
