<?php

namespace LaravelEnso\DataImport\app\Contracts;

use LaravelEnso\Helpers\app\Classes\Obj;

interface Importable
{
    public function run(Obj $row, Obj $params);
}
