<?php

namespace LaravelEnso\DataImport\Enums;

use Illuminate\Support\Facades\Config;
use LaravelEnso\DataImport\Exceptions\DataImport;
use LaravelEnso\Enums\Services\Enum;
use Throwable;

class Types extends Enum
{
    protected static function data(): array
    {
        $configs = Config::get('enso.imports.configs');

        try {
            return array_combine(
                array_keys($configs),
                array_column($configs, 'label')
            );
        } catch (Throwable $exception) {
            throw DataImport::configNotReadable();
        }
    }
}
