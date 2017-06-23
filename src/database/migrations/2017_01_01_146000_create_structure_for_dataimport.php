<?php

use LaravelEnso\StructureManager\app\Classes\StructureMigration;

class CreateStructureForDataImport extends StructureMigration
{
    protected $permissionGroup = [
        'name' => 'import', 'description' => 'Data Import Group',
    ];

    protected $permissions = [
        ['name' => 'import.index', 'description' => 'Imports index', 'type' => 0, 'default' => false],
        ['name' => 'import.getImportData', 'description' => 'Show Import', 'type' => 0, 'default' => false],
        ['name' => 'import.run', 'description' => 'Run Import', 'type' => 1, 'default' => false],
        ['name' => 'import.destroy', 'description' => 'Delete Import', 'type' => 1, 'default' => false],
        ['name' => 'import.download', 'description' => 'Download Import', 'type' => 0, 'default' => false],
        ['name' => 'import.initTable', 'description' => 'Init Table for Import', 'type' => 0, 'default' => false],
        ['name' => 'import.getTableData', 'description' => 'Table Data for Import', 'type' => 0, 'default' => false],
        ['name' => 'import.getSummary', 'description' => 'Summary for Import', 'type' => 0, 'default' => false],
    ];

    protected $menu = [
        'name' => 'Data Import', 'icon' => 'fa fa-fw fa-cloud-upload', 'link' => 'import', 'has_children' => 0,
    ];

    protected $parentMenu = '';
}
