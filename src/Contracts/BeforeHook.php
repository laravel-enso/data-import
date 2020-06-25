<?php

namespace LaravelEnso\DataImport\Contracts;

use LaravelEnso\Core\Models\User;
use LaravelEnso\Helpers\Classes\Obj;

interface BeforeHook
{
    public function before(User $user, Obj $params);
}
