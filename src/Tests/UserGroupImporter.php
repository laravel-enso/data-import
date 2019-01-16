<?php

namespace LaravelEnso\DataImport\Tests;

use LaravelEnso\Core\app\Models\UserGroup;
use LaravelEnso\DataImport\app\Contracts\Importable;

class UserGroupImporter implements Importable
{
    public function run(Obj $row)
    {
        UserGroup::create($row->all());
    }
}
