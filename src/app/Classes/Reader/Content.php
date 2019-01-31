<?php

namespace LaravelEnso\DataImport\app\Classes\Reader;

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
        return $this->sheet($sheetName)->getRowIterator();
    }
}
