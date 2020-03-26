<?php

namespace LaravelEnso\DataImport\App\Contracts;

use LaravelEnso\Core\App\Models\User;
use LaravelEnso\Helpers\App\Classes\Obj;

interface Importable
{
    public function run(Obj $row, User $user, Obj $params);
}
