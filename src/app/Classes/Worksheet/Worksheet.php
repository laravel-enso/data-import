<?php

namespace LaravelEnso\DataImport\app\Classes\Worksheet;

class Worksheet
{
    private $sheets;

    public function __construct()
    {
        $this->sheets = collect();
    }

    public function push(Sheet $sheet)
    {
        $this->sheets->push($sheet);
    }

    public function sheets()
    {
        return $this->sheets;
    }

    public function sheetNames()
    {
        return $this->sheets->map(function ($sheet) {
            return $sheet->name();
        });
    }
}
