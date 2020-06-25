<?php

namespace LaravelEnso\DataImport\Enums;

use Exception;
use LaravelEnso\DataImport\Exceptions\DataImport;
use LaravelEnso\Enums\Services\Enum;

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
