<?php

namespace LaravelEnso\DataImport\app\Enums;

use LaravelEnso\Helpers\app\Classes\Enum;

class ImportTypes extends Enum
{
    public function __construct()
    {
        static::$data = array_combine(
            array_keys(config('enso.imports')),
            array_column(config('enso.imports'), 'label')
        );
    }
}
