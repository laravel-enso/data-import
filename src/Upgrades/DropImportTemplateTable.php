<?php

namespace LaravelEnso\DataImport\Upgrades;

use Illuminate\Support\Facades\Schema;
use LaravelEnso\Upgrade\Contracts\MigratesTable;

class DropImportTemplateTable implements MigratesTable
{
    public function isMigrated(): bool
    {
        return ! Schema::hasTable('import_templates');
    }

    public function migrateTable(): void
    {
        Schema::dropIfExists('import_templates');
    }
}
