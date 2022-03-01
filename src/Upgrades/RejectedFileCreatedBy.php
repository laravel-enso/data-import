<?php

namespace LaravelEnso\DataImport\Upgrades;

use Illuminate\Support\Facades\DB;
use LaravelEnso\Upgrade\Contracts\BeforeMigration;
use LaravelEnso\Upgrade\Contracts\MigratesData;

class RejectedFileCreatedBy implements MigratesData, BeforeMigration
{
    public function isMigrated(): bool
    {
        $result = DB::select("
            SELECT * FROM `files`
            JOIN rejected_imports ON rejected_imports.id = files.attachable_id
            JOIN data_imports ON data_imports.id = rejected_imports.data_import_id
            WHERE files.created_by IS NULL
                AND data_imports.created_by IS NOT NULL
                AND attachable_type = 'rejectedImport'
            LIMIT 1
        ");

        return count($result) === 0;
    }

    public function migrateData(): void
    {
        DB::update("
            UPDATE `files`
            JOIN rejected_imports ON rejected_imports.id = files.attachable_id
            JOIN data_imports ON data_imports.id = rejected_imports.data_import_id
            SET  files.created_by = data_imports.created_by
            WHERE files.created_by IS NULL
                AND data_imports.created_by IS NOT NULL
                AND attachable_type = 'rejectedImport'
        ");
    }
}
