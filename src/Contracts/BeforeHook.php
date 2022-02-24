<?php

namespace LaravelEnso\DataImport\Contracts;

use LaravelEnso\DataImport\Models\Import;

interface BeforeHook
{
    public function before(Import $import);
}
