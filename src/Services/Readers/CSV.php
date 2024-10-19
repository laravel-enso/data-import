<?php

namespace LaravelEnso\DataImport\Services\Readers;

use OpenSpout\Reader\CSV\Options;
use OpenSpout\Reader\CSV\Reader as CSVReader;
use OpenSpout\Reader\CSV\RowIterator;
use OpenSpout\Reader\CSV\Sheet;

class CSV extends Reader
{
    public function __construct(
        protected string $file,
        protected string $delimiter,
        protected string $enclosure,
    ) {
        parent::__construct($file);

        $this->reader = $this->reader($delimiter, $enclosure);
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

    private function reader(string $delimiter, string $enclosure): CSVReader
    {
        $options = new Options();
        $options->FIELD_DELIMITER = $delimiter;
        $options->FIELD_ENCLOSURE = $enclosure;

        return new CSVReader($options);
    }
}
