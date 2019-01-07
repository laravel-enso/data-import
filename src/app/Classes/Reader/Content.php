<?php

namespace LaravelEnso\DataImport\app\Classes\Reader;

class Content extends XLSX
{
    private $ready = false;

    public function rowIterator($sheetName)
    {
        $this->init();

        $iterator = $this->sheet($sheetName)->getRowIterator();
        $iterator->rewind();
        $iterator->next();

        return $iterator;
    }

    private function init()
    {
        if (! $this->ready) {
            $this->read();
            $this->ready = true;
        }
    }
}
