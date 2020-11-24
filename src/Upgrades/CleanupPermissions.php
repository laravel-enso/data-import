<?php

namespace LaravelEnso\DataImport\Upgrades;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use LaravelEnso\Permissions\Models\Permission;
use LaravelEnso\Roles\Models\Role;
use LaravelEnso\Upgrade\Contracts\MigratesData;
use LaravelEnso\Upgrade\Contracts\Prioritization;

class CleanupPermissions implements MigratesData, Prioritization
{
    const Permissions = [
        'import.downloadTemplate', 'import.uploadTemplate', 'import.deleteTemplate',
    ];

    public function isMigrated(): bool
    {
        return ! Permission::whereName('import.downloadTemplate')->exists();
    }

    public function migrateData(): void
    {
        Permission::whereIn('name', static::Permissions)
            ->delete();

        Permission::whereName('import.template')
            ->update(['description' => 'Download import template']);

        Permission::whereName('import.downloadRejected')
            ->update(['name' => 'import.rejected']);

        if (App::isLocal()) {
            Role::get()
                ->reject(fn ($role) => $role->name === Config::get('enso.config.defaultRole'))
                ->each->writeConfig();
        }
    }

    public function priority(): int
    {
        return 0;
    }
}
