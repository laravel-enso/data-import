<?php

namespace LaravelEnso\DataImport\Services\Readers;

use Illuminate\Support\Collection;
use LaravelEnso\DataImport\Services\Sanitizers\Sanitize;
use OpenSpout\Reader\XLSX\Reader as XLSXReader;
use OpenSpout\Reader\XLSX\RowIterator;
use OpenSpout\Reader\XLSX\Sheet;

class XLSX extends Reader
{
    public function __construct(protected string $file)
    {
        parent::__construct($file);
        $this->reader = new XLSXReader();
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
}
