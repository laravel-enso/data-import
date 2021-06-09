<?php

namespace LaravelEnso\DataImport\Contracts;

use LaravelEnso\DataImport\Models\DataImport;

interface AfterHook
{
    public function after(DataImport $import);
}
