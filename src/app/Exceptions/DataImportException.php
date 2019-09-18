<?php

namespace LaravelEnso\DataImport\app\Exceptions;

use LaravelEnso\Helpers\app\Exceptions\EnsoException;

class DataImportException extends EnsoException
{
    public static function configNotReadable()
    {
        throw new static(__('"imports.php" config file is not readable'));
    }

    public static function deleteRunningImport()
    {
        throw new static(__('The import is currently running and cannot be deleted'));
    }

    public function fileNotReadable()
    {
        throw new static(__('Unable to read file'));
    }
}
