<?php

namespace LaravelEnso\DataImport\app\Enums;

use LaravelEnso\Helpers\Classes\Enum;

class ImportTypes extends Enum
{
    protected static $data;

    public function __construct()
    {
        self::$data = array_combine(
            array_keys(config('enso.importing.configs')),
            array_column(config('enso.importing.configs'), 'label')
        );
    }
}
