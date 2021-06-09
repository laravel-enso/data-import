<?php

namespace LaravelEnso\DataImport\Contracts;

use LaravelEnso\DataImport\Models\DataImport;
use LaravelEnso\Helpers\Services\Obj;

interface Importable
{
    public function run(Obj $row, DataImport $import);
}
