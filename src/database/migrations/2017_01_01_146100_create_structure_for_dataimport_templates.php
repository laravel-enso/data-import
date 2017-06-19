<?php

use LaravelEnso\Core\app\Classes\StructureManager\StructureMigration;

class CreateStructureForDataImportTemplates extends StructureMigration
{
    protected $permissionsGroup = [
        'name' => 'import', 'description' => 'Data Import Group',
    ];

    protected $permissions = [
        ['name' => 'import.getTemplate', 'description' => 'Get Import Template', 'type' => 0, 'default' => false],
        ['name' => 'import.uploadTemplate', 'description' => 'Upload Import Template', 'type' => 1, 'default' => false],
        ['name' => 'import.deleteTemplate', 'description' => 'Delete Import Template', 'type' => 1, 'default' => false],
        ['name' => 'import.downloadTemplate', 'description' => 'Download Import Template', 'type' => 0, 'default' => false],
    ];
}
