<?php

namespace LaravelEnso\DataImport\Services\Readers;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\CSV\RowIterator;
use Box\Spout\Reader\CSV\Sheet;

class CSV extends Reader
{
    public function __construct(
        protected string $file,
        protected string $delimiter,
        protected string $enclosure,
    ) {
        parent::__construct($file);
        $this->reader = ReaderEntityFactory::createCSVReader();
        $this->reader->setFieldDelimiter($delimiter);
        $this->reader->setFieldEnclosure($enclosure);
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
