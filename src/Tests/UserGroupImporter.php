<?php

namespace LaravelEnso\DataImport\Tests;

use LaravelEnso\Core\app\Models\UserGroup;
use LaravelEnso\DataImport\app\Contracts\Importer;

class UserGroupImporter implements Importer
{
    public function run(Obj $row)
    {
        UserGroup::create($row->all());
    }
}
