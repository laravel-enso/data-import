<?php

namespace LaravelEnso\DataImport\Contracts;

use LaravelEnso\Core\Models\User;
use LaravelEnso\Helpers\Services\Obj;

interface AfterHook
{
    public function after(User $user, Obj $params);
}
