<?php

namespace LaravelEnso\DataImport\app\Exceptions;

use LaravelEnso\Helpers\app\Exceptions\EnsoException;

class DataImportException extends EnsoException
{
    public static function configNotReadable()
    {
        throw new static(__('"imports.php" config file is not readable'));
    }
}
