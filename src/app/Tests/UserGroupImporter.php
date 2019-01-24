<?php

namespace LaravelEnso\DataImport\app\Tests;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Core\app\Models\UserGroup;
use LaravelEnso\DataImport\app\Contracts\Importable;

class UserGroupImporter implements Importable
{
    public function run(Obj $row, Obj $params)
    {
        UserGroup::create($row->all());
    }
}
