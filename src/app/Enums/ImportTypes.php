<?php

namespace LaravelEnso\DataImport\app\Enums;

use Exception;
use LaravelEnso\Enums\app\Services\Enum;
use LaravelEnso\DataImport\app\Exceptions\DataImportException;

class ImportTypes extends Enum
{
    public static function attributes()
    {
        try {
            $data = array_combine(
                array_keys(config('enso.imports.configs')),
                array_column(config('enso.imports.configs'), 'label')
            );
        } catch (Exception $e) {
            throw DataImportException::configNotReadable();
        }

        return $data;
    }
}
