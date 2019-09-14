<?php

namespace LaravelEnso\DataImport\app\Tests;

use LaravelEnso\Core\app\Models\User;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Core\app\Models\UserGroup;
use LaravelEnso\DataImport\app\Contracts\Importable;

class UserGroupImporter implements Importable
{
    public function run(Obj $row, User $user, Obj $params)
    {
        UserGroup::create($row->all());
    }
}
