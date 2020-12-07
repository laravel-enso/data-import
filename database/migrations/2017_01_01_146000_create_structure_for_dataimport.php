<?php

use LaravelEnso\Migrator\Database\Migration;

class CreateStructureForDataImport extends Migration
{
    protected array $permissions = [
        ['name' => 'import.index', 'description' => 'Imports index', 'is_default' => false],

        ['name' => 'import.store', 'description' => 'Upload file for import', 'is_default' => false],
        ['name' => 'import.destroy', 'description' => 'Delete import', 'is_default' => false],
        ['name' => 'import.download', 'description' => 'Download import', 'is_default' => false],

        ['name' => 'import.initTable', 'description' => 'Init table for imports', 'is_default' => false],
        ['name' => 'import.tableData', 'description' => 'Table data for imports', 'is_default' => false],
        ['name' => 'import.exportExcel', 'description' => 'Export excel for imports', 'is_default' => false],

        ['name' => 'import.rejected', 'description' => 'Download rejected summary for import', 'is_default' => false],

        ['name' => 'import.template', 'description' => 'Download import template', 'is_default' => false],

        ['name' => 'import.show', 'description' => 'Get import', 'is_default' => false],

        ['name' => 'import.cancel', 'description' => 'Cancel import', 'is_default' => false],
        ['name' => 'import.restart', 'description' => 'Restart import', 'is_default' => false],
    ];

    protected array $menu = [
        'name' => 'Data Import', 'icon' => 'cloud-upload-alt', 'route' => 'import.index', 'order_index' => 800, 'has_children' => false,
    ];
}
