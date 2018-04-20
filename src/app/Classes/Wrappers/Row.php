<?php

namespace LaravelEnso\DataImport\app\Classes\Wrappers;

use Illuminate\Support\Collection;

class Row extends Collection
{

    public function __get($property)
    {
        return $this[$property];
    }

}
