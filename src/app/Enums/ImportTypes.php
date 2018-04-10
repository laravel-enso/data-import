<?php

namespace LaravelEnso\DataImport\app\Enums;

use Exception;
use LaravelEnso\Helpers\app\Classes\Enum;
use LaravelEnso\DataImport\app\Exceptions\ConfigException;

class ImportTypes extends Enum
{
    public static function attributes()
    {
        try {
            $data = array_combine(
                array_keys(config('enso.imports')),
                array_column(config('enso.imports'), 'label')
            );
        } catch (Exception $e) {
            throw new ConfigException(__('Imports config file is not readable'));
        }

        return $data;
    }
}
