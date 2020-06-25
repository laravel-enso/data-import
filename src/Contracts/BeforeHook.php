<?php

namespace LaravelEnso\DataImport\Contracts;

use LaravelEnso\Core\Models\User;
use LaravelEnso\Helpers\Services\Obj;

interface BeforeHook
{
    public function before(User $user, Obj $params);
}
