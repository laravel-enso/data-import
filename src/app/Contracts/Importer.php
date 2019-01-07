<?php

namespace LaravelEnso\DataImport\app\Contracts;

use LaravelEnso\Helpers\app\Classes\Obj;

interface Importer
{
    public function run(Obj $row);
}
