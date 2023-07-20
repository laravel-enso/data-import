<?php

namespace LaravelEnso\DataImport\Services\Readers;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\XLSX\Reader as XLSXReader;
use Box\Spout\Reader\XLSX\RowIterator;
use Box\Spout\Reader\XLSX\Sheet;
use Illuminate\Support\Collection;
use LaravelEnso\DataImport\Services\Sanitizers\Sanitize;

class XLSX extends Reader
{
    protected bool $open;
    protected XLSXReader $reader;

    public function __construct(protected string $file)
    {
        $this->open = false;
        $this->reader = ReaderEntityFactory::createXLSXReader();
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
