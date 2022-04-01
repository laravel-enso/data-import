<?php

namespace LaravelEnso\DataImport\Upgrades;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use LaravelEnso\Upgrade\Contracts\MigratesTable;
use LaravelEnso\Upgrade\Contracts\ShouldRunManually;

class UpdateBatchConstraint implements MigratesTable, ShouldRunManually
{
    public function isMigrated(): bool
    {
        return false;
    }

    public function migrateTable(): void
    {
        Schema::table('data_imports', function (Blueprint $table) {
            $table->dropForeign(['batch']);
            $table->foreign('batch')->references('id')->on('job_batches')
                ->onUpdate('restrict')->onDelete('SET NULL');
        });
    }
}
