<?php

namespace LaravelEnso\DataImport\app\Classes\Reader;

use Illuminate\Support\Collection;

class Sheet
{
    private $name;
    private $rows;

    public function __construct(string $name, Collection $rows)
    {
        $this->name = $name;
        $this->rows = $rows;
    }

    public function name()
    {
        return $this->name;
    }

    public function rows()
    {
        return $this->rows;
    }
}
