<?php

namespace LaravelEnso\DataImport\Tests;

use LaravelEnso\Core\Models\User;
use LaravelEnso\Core\Models\UserGroup;
use LaravelEnso\DataImport\Contracts\Importable;
use LaravelEnso\Helpers\Services\Obj;

class UserGroupImporter implements Importable
{
    public function run(Obj $row, User $user, Obj $params)
    {
        UserGroup::create($row->all());
    }
}
