<?php

namespace LaravelEnso\DataImport\Upgrades;

use LaravelEnso\Upgrade\Contracts\MigratesStructure;
use LaravelEnso\Upgrade\Traits\StructureMigration;

class Permissions implements MigratesStructure
{
    use StructureMigration;

    protected $permissions = [
        ['name' => 'import.show', 'description' => 'Get import', 'is_default' => false],
    ];

    protected $roles = ['admin', 'supervisor'];
}
