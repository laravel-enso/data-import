<?php

namespace LaravelEnso\DataImport\Contracts;

use LaravelEnso\Core\Models\User;
use LaravelEnso\Helpers\Classes\Obj;

interface Importable
{
    public function run(Obj $row, User $user, Obj $params);
}
