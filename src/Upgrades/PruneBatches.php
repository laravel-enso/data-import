<?php

namespace LaravelEnso\DataImport\Upgrades;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use LaravelEnso\DataImport\Models\Import;
use LaravelEnso\Upgrade\Contracts\MigratesData;

class PruneBatches implements MigratesData
{
    public function priority(): int
    {
        return 100;
    }

    public function isMigrated(): bool
    {
        return $this->batches()->doesntExist();
    }

    public function migrateData(): void
    {
        Import::whereNotNull('batch')->update(['batch' => null]);

        $this->batches()->delete();
    }

    private function batches(): Builder
    {
        return DB::table('job_batches');
    }
}
