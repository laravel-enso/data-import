<?php

namespace LaravelEnso\DataImport\app\Classes\Reader;

use LaravelEnso\DataImport\app\Classes\Worksheet\Sheet;
use LaravelEnso\DataImport\app\Classes\Worksheet\Worksheet;

class Structure extends XLSX
{
    private $worksheet;

    public function get()
    {
        $this->worksheet = new Worksheet();

        $this->open()
            ->build()
            ->close();

        return $this->worksheet;
    }

    private function build()
    {
        foreach ($this->sheetIterator() as $sheet) {
            $this->worksheet->push($this->worksheet($sheet));
        }

        return $this;
    }

    private function worksheet($sheet)
    {
        $rowIterator = $sheet->getRowIterator();
        $rowIterator->rewind();

        return new Sheet(
            $this->normalizeSheet($sheet->getName()),
            $this->normalizeHeader($rowIterator->current())
        );
    }
}
