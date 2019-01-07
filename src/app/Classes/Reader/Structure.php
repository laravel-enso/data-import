<?php

namespace LaravelEnso\DataImport\app\Classes\Reader;

use Illuminate\Support\Str;
use LaravelEnso\DataImport\app\Classes\Worksheet\Sheet;
use LaravelEnso\DataImport\app\Classes\Worksheet\Worksheet;

class Structure extends XLSX
{
    private $worksheet;

    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this->worksheet = new Worksheet();
    }

    public function get()
    {
        $this->read()
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
            $this->normalize($sheet->getName()),
            $rowIterator->current()
        );
    }

    private function normalize($string)
    {
        return Str::camel(Str::lower(($string)));
    }
}
