<?php

namespace LaravelEnso\DataImport\Upgrades;

use Illuminate\Support\Collection;
use LaravelEnso\Permissions\Models\Permission;
use LaravelEnso\Upgrade\Contracts\MigratesData;

class CleanupPermissions implements MigratesData
{
    const Permissions = [
        ['name' => 'import.template', 'description' => 'Get import template', 'is_default' => false],
        ['name' => 'import.uploadTemplate', 'description' => 'Upload import template', 'is_default' => false],
        ['name' => 'import.deleteTemplate', 'description' => 'Delete import template', 'is_default' => false],
    ];

    public function isMigrated(): bool
    {
        return ! $this->query()->exists();
    }

    public function migrateData(): void
    {
        $this->query()->delete();
    }

    private function query()
    {
        return Permission::whereIn('name', Collection::wrap(static::Permissions)->pluck('name'));
    }
}
