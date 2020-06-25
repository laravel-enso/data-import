<?php

namespace LaravelEnso\DataImport\Contracts;

use LaravelEnso\Core\Models\User;
use LaravelEnso\Helpers\Classes\Obj;

interface AfterHook
{
    public function after(User $user, Obj $params);
}
