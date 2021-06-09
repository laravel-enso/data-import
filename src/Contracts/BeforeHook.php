<?php

namespace LaravelEnso\DataImport\Contracts;

use LaravelEnso\DataImport\Models\DataImport;

interface BeforeHook
{
    public function before(DataImport $import);
}
