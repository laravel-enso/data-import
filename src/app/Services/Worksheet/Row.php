<?php

namespace LaravelEnso\DataImport\app\Services\Worksheet;

use LaravelEnso\Helpers\app\Classes\Obj;

class Row extends Obj
{
    public function isRejected()
    {
        return $this->has(config('enso.imports.errorColumn'));
    }
}
