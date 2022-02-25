<?php

namespace LaravelEnso\DataImport\Tests;

use LaravelEnso\DataImport\Contracts\Importable;
use LaravelEnso\DataImport\Models\Import;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\UserGroups\Models\UserGroup;

class UserGroupImporter implements Importable
{
    public function run(Obj $row, Import $import)
    {
        UserGroup::create($row->all());
    }
}
