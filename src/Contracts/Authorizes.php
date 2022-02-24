<?php

namespace LaravelEnso\DataImport\Contracts;

use LaravelEnso\DataImport\Models\Import;

interface Authorizes extends Authenticates
{
    public function authorizes(Import $import): bool;
}
