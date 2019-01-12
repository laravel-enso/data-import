<?php

namespace LaravelEnso\DataImport\app\Classes\Reader;

class Content extends XLSX
{
    public function rowIterator($sheetName)
    {
        $iterator = $this->sheet($sheetName)->getRowIterator();
        $iterator->rewind();
        $iterator->next();

        return $iterator;
    }
}
