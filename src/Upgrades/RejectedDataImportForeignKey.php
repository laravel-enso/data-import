<?php

namespace LaravelEnso\DataImport\Upgrades;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use LaravelEnso\Upgrade\Contracts\MigratesTable;
use LaravelEnso\Upgrade\Contracts\Prioritization;
use LaravelEnso\Upgrade\Helpers\Table;

class RejectedDataImportForeignKey implements MigratesTable, Prioritization
{
    private const Table = 'rejected_imports';
    private const ForeignKey = 'rejected_imports_data_import_id_foreign';

    public function isMigrated(): bool
    {
        return ! Table::hasForeignKey(self::Table, self::ForeignKey)
            || Table::foreignKey(self::Table, self::ForeignKey)
                ->getOption('onDelete') !== 'CASCADE';
    }

    public function migrateTable(): void
    {
        Schema::table(self::Table, function (Blueprint $table) {
            $table->dropForeign(self::ForeignKey);
            $table->foreign('import_id')->references('id')->on('data_imports')
                ->onDelete('restrict');
        });
    }

    public function priority(): int
    {
        return 152;
    }
}
