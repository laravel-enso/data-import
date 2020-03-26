<?php

namespace LaravelEnso\DataImport\App\Tests;

use LaravelEnso\Core\App\Models\User;
use LaravelEnso\Core\App\Models\UserGroup;
use LaravelEnso\DataImport\App\Contracts\Importable;
use LaravelEnso\Helpers\App\Classes\Obj;

class UserGroupImporter implements Importable
{
    public function run(Obj $row, User $user, Obj $params)
    {
        UserGroup::create($row->all());
    }
}
