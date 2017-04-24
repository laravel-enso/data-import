<?php

use LaravelEnso\Core\app\Classes\StructureManager\StructureMigration;

class CreateStructureForDataImport extends StructureMigration
{
    protected $permissionsGroup = [
        'name' => 'import', 'description' => 'Data Import Group',
    ];

    protected $permissions = [
        ['name' => 'import.index', 'description' => 'Imports index', 'type' => 0],
        ['name' => 'import.getImportData', 'description' => 'Show Import', 'type' => 0],
        ['name' => 'import.run', 'description' => 'Run Import', 'type' => 1],
        ['name' => 'import.destroy', 'description' => 'Delete Import', 'type' => 1],
        ['name' => 'import.download', 'description' => 'Download Import', 'type' => 0],
        ['name' => 'import.initTable', 'description' => 'Init Table for Import', 'type' => 0],
        ['name' => 'import.getTableData', 'description' => 'Table Data for Import', 'type' => 0],
        ['name' => 'import.getSummary', 'description' => 'Summary for Import', 'type' => 0],
    ];

    protected $menu = [
        'name' => 'Data Import', 'icon' => 'fa fa-fw fa-cloud-upload', 'link' => 'import', 'has_children' => 0,
    ];

    protected $parentMenu = '';
}
