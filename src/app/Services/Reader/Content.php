<?php

namespace LaravelEnso\DataImport\app\Services\Reader;

class Content extends XLSX
{
    public function header($sheetName)
    {
        $iterator = $this->sheet($sheetName)->getRowIterator();
        $iterator->rewind();

        return $this->normalizeHeader($iterator->current());
    }

    public function rowIterator($sheetName)
    {
        $iterator = $this->sheet($sheetName)->getRowIterator();
        $iterator->rewind();
        $iterator->next();

        return $iterator;
    }
}
