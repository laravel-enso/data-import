<?php

namespace LaravelEnso\DataImport\app\Enums;

use LaravelEnso\Helpers\app\Classes\Enum;

class ImportTypes extends Enum
{
    protected static $data;

    public function __construct()
    {
        self::$data = array_combine(
            array_keys(config('enso.imports')),
            array_column(config('enso.imports'), 'label')
        );
    }
}
