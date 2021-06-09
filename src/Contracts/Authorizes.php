<?php

namespace LaravelEnso\DataImport\Contracts;

use LaravelEnso\DataImport\Models\DataImport;

interface Authorizes extends Authenticates
{
    public function authorizes(DataImport $import): bool;
}
