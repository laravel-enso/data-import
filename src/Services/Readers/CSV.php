<?php

namespace LaravelEnso\DataImport\Services\Readers;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\CSV\Reader  as CSVReader;
use Box\Spout\Reader\CSV\RowIterator;
use Box\Spout\Reader\CSV\Sheet;

class CSV extends Reader
{
    protected bool $open;
    protected CSVReader $reader;

    public function __construct(protected string $file)
    {
        $this->open = false;
        $this->reader = ReaderEntityFactory::createCSVReader();
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
