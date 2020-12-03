<?php

namespace LaravelEnso\DataImport\Upgrades;

use Illuminate\Support\Facades\Schema;
use LaravelEnso\Upgrade\Contracts\MigratesTable;
use LaravelEnso\Upgrade\Helpers\Table;

class AddsDataImportBatch implements MigratesTable
{
    public function isMigrated(): bool
    {
        return Table::hasColumn('data_imports', 'batch');
    }

    public function migrateTable(): void
    {
        Schema::table('data_imports', function ($table) {
            $table->string('batch')->after('id')->nullable();
            $table->foreign('batch')->references('id')->on('job_batches');
        });
    }
}
