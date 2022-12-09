<?php

namespace LaravelEnso\DataImport\Upgrades;

use LaravelEnso\Upgrade\Contracts\MigratesStructure;
use LaravelEnso\Upgrade\Traits\StructureMigration;

class Options implements MigratesStructure
{
    use StructureMigration;

    protected $permissions = [
        ['name' => 'import.options', 'description' => 'Get import options for select', 'is_default' => false],
    ];
}
