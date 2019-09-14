<?php

namespace LaravelEnso\DataImport\app\Contracts;

use LaravelEnso\Core\app\Models\User;
use LaravelEnso\Helpers\app\Classes\Obj;

interface BeforeHook
{
    public function before(User $user, Obj $params);
}
