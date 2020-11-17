<?php

namespace LaravelEnso\DataImport\Upgrades;

use Illuminate\Support\Collection;
use LaravelEnso\Permissions\Models\Permission;
use LaravelEnso\Upgrade\Contracts\MigratesData;
use LaravelEnso\Upgrade\Contracts\Prioritization;

class CleanupPermissions implements MigratesData, Prioritization
{
    const Permissions = [
        ['name' => 'import.template', 'description' => 'Get import template', 'is_default' => false],
        ['name' => 'import.uploadTemplate', 'description' => 'Upload import template', 'is_default' => false],
        ['name' => 'import.deleteTemplate', 'description' => 'Delete import template', 'is_default' => false],
    ];

    public function isMigrated(): bool
    {
        return ! Permission::whereName('import.uploadTemplate')->exists();
    }

    public function migrateData(): void
    {
        Permission::whereIn('name', Collection::wrap(static::Permissions)->pluck('name'))
            ->delete();
    }

    public function priority(): int
    {
        return 0;
    }
}
