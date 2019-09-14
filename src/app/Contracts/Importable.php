<?php

namespace LaravelEnso\DataImport\app\Contracts;

use LaravelEnso\Core\app\Models\User;
use LaravelEnso\Helpers\app\Classes\Obj;

interface Importable
{
    public function run(Obj $row, User $user, Obj $params);
}
