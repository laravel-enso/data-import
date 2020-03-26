<?php

namespace LaravelEnso\DataImport\App\Contracts;

use LaravelEnso\Core\App\Models\User;
use LaravelEnso\Helpers\App\Classes\Obj;

interface BeforeHook
{
    public function before(User $user, Obj $params);
}
