<?php

namespace LaravelEnso\DataImport\Tests;

use LaravelEnso\Core\app\Models\UserGroup;
use LaravelEnso\DataImport\app\Classes\Importers\Importer;

class UserGroupImporter extends Importer
{
    public function run()
    {
        \DB::transaction(function () {
            $this->rowsFromSheet('groups')
                ->each(function ($row) {
                    $this->importRow($row);
                });
        });
    }

    private function importRow($row)
    {
        UserGroup::create($row->toArray());

        $this->incSuccess();
    }
}
