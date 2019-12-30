<?php

namespace LaravelEnso\DataImport\App\Exceptions;

use LaravelEnso\Helpers\App\Exceptions\EnsoException;

class DataImport extends EnsoException
{
    public static function configNotReadable()
    {
        return new static(__('"imports.php" config file is not readable'));
    }

    public static function deleteRunningImport()
    {
        return new static(__('The import is currently running and cannot be deleted'));
    }

    public static function fileNotReadable()
    {
        return new static(__('Unable to read file'));
    }
}
