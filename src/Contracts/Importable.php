<?php

namespace LaravelEnso\DataImport\Contracts;

use LaravelEnso\DataImport\Models\Import;
use LaravelEnso\Helpers\Services\Obj;

interface Importable
{
    public function run(Obj $row, Import $import);
}
