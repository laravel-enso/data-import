<?php

namespace LaravelEnso\DataImport\Services\Readers;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\CSV\Reader;
use Box\Spout\Reader\CSV\RowIterator;
use Box\Spout\Reader\CSV\Sheet;
use LaravelEnso\DataImport\Services\Readers\Reader as ReaderInterface;


class CSV extends ReaderInterface
{
    protected bool $open;
    protected Reader $reader;

    public function __construct(protected string $file)
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

    public function rowIterator(): RowIterator
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
}
