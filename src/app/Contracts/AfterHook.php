<?php

namespace LaravelEnso\DataImport\app\Contracts;

use LaravelEnso\Core\app\Models\User;
use LaravelEnso\Helpers\app\Classes\Obj;

interface AfterHook
{
    public function after(User $user, Obj $params);
}
