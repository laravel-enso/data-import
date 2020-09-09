<?php

namespace LaravelEnso\DataImport\Contracts;

use LaravelEnso\Core\Models\User;
use LaravelEnso\Helpers\Services\Obj;

interface Authorizes extends Authenticates
{
    public function authorizes(User $user, Obj $params): bool;
}
