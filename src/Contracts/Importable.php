<?php

namespace LaravelEnso\DataImport\Contracts;

use LaravelEnso\Core\Models\User;
use LaravelEnso\Helpers\Services\Obj;

interface Importable
{
    public function run(Obj $row, User $user, Obj $params);
}
