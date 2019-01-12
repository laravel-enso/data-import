<?php

namespace LaravelEnso\DataImport\app\Classes\Worksheet;

use LaravelEnso\Helpers\app\Classes\Obj;

class Row extends Obj
{
    public function isRejected()
    {
        return $this->has(config('enso.imports.errorColumn'));
    }
}
