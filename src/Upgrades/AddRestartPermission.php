<?php

namespace LaravelEnso\DataImport\Upgrades;

use LaravelEnso\Upgrade\Contracts\MigratesStructure;

class AddRestartPermission implements MigratesStructure
{
    public function permissions(): array
    {
        return [
            ['name' => 'import.restart', 'description' => 'Restart import', 'is_default' => false],
        ];
    }

    public function roles(): array
    {
        return ['admin', 'supervisor'];
    }
}
