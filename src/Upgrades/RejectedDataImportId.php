<?php

namespace LaravelEnso\DataImport\Upgrades;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LaravelEnso\DataImport\Models\Import;
use LaravelEnso\Upgrade\Contracts\MigratesData;
use LaravelEnso\Upgrade\Contracts\MigratesTable;
use LaravelEnso\Upgrade\Contracts\Prioritization;
use LaravelEnso\Upgrade\Helpers\Table;

class RejectedDataImportId implements MigratesTable, Prioritization
{
    public function isMigrated(): bool
    {
        return Table::hasColumn('rejected_imports', 'import_id');
    }

    public function migrateTable(): void
    {
        Schema::create('rejected_imports', function (Blueprint $table) {
            $table->renameColumn('data_import_id', 'import_id');
        });
    }

    public function priority(): int
    {
        return 150;
    }
}
