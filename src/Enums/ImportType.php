<?php

namespace LaravelEnso\DataImport\Enums;

use LaravelEnso\Enums\Services\Enum;

class ImportType extends Enum
{
    public const Update = 'update';
    public const Insert = 'insert';
}
