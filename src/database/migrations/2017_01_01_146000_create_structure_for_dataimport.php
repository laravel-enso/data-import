<?php

use LaravelEnso\StructureManager\app\Classes\StructureMigration;

class CreateStructureForDataImport extends StructureMigration
{
    protected $permissions = [
        ['name' => 'import.index', 'description' => 'Imports index', 'type' => 0, 'is_default' => false],
        ['name' => 'import.run', 'description' => 'Run import', 'type' => 1, 'is_default' => false],
        ['name' => 'import.destroy', 'description' => 'Delete import', 'type' => 1, 'is_default' => false],
        ['name' => 'import.download', 'description' => 'Download import', 'type' => 0, 'is_default' => false],
        ['name' => 'import.initTable', 'description' => 'Init table for imports', 'type' => 0, 'is_default' => false],
        ['name' => 'import.tableData', 'description' => 'Table data for imports', 'type' => 0, 'is_default' => false],
        ['name' => 'import.exportExcel', 'description' => 'Export excel for imports', 'type' => 0, 'is_default' => false],
        ['name' => 'import.getSummary', 'description' => 'Summary for import', 'type' => 0, 'is_default' => false],
        ['name' => 'import.getTemplate', 'description' => 'Get import template', 'type' => 0, 'is_default' => false],
        ['name' => 'import.uploadTemplate', 'description' => 'Upload import template', 'type' => 1, 'is_default' => false],
        ['name' => 'import.deleteTemplate', 'description' => 'Delete import template', 'type' => 1, 'is_default' => false],
        ['name' => 'import.downloadTemplate', 'description' => 'Download import template', 'type' => 0, 'is_default' => false],
    ];

    protected $menu = [
        'name' => 'Data Import', 'icon' => 'cloud-upload-alt', 'route' => 'import.index', 'order_index' => 400, 'has_children' => false,
    ];

    protected $parentMenu = '';
}
