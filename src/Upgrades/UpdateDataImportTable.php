<?php

namespace LaravelEnso\DataImport\Upgrades;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use LaravelEnso\Upgrade\Contracts\MigratesTable;
use LaravelEnso\Upgrade\Helpers\Table;

class UpdateDataImportTable implements MigratesTable
{
    public function isMigrated(): bool
    {
        return Table::hasColumn('data_imports', 'batch');
    }

    public function migrateTable(): void
    {
        Schema::table('data_imports', function (Blueprint $table) {
            $table->string('batch')->after('id')->nullable();
            $table->foreign('batch')->references('id')->on('job_batches');
            $table->dropColumn('file_parsed');
            $table->dropColumn('chunks');
            $table->dropColumn('processed_chunks');
        });
    }
}
