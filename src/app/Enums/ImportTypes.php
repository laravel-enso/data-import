<?php

namespace LaravelEnso\DataImport\App\Enums;

use Exception;
use LaravelEnso\DataImport\App\Exceptions\DataImport;
use LaravelEnso\Enums\App\Services\Enum;

class ImportTypes extends Enum
{
    protected static function data(): array
    {
        try {
            $data = array_combine(
                array_keys(config('enso.imports.configs')),
                array_column(config('enso.imports.configs'), 'label')
            );
        } catch (Exception $exception) {
            throw DataImport::configNotReadable();
        }

        return $data;
    }
}
