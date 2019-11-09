<?php

namespace LaravelEnso\DataImport\app\Tests;

use LaravelEnso\Core\app\Models\User;
use LaravelEnso\Core\app\Models\UserGroup;
use LaravelEnso\DataImport\app\Contracts\Importable;
use LaravelEnso\Helpers\app\Classes\Obj;

class UserGroupImporter implements Importable
{
    public function run(Obj $row, User $user, Obj $params)
    {
        UserGroup::create($row->all());
    }
}
