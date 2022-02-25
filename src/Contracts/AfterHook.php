<?php

namespace LaravelEnso\DataImport\Contracts;

use LaravelEnso\DataImport\Models\Import;

interface AfterHook
{
    public function after(Import $import);
}
