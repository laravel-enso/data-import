<?php

namespace LaravelEnso\DataImport\App\Contracts;

use LaravelEnso\Core\App\Models\User;
use LaravelEnso\Helpers\App\Classes\Obj;

interface AfterHook
{
    public function after(User $user, Obj $params);
}
