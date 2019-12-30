<?php

use LaravelEnso\Migrator\App\Database\Migration;
use LaravelEnso\Permissions\App\Enums\Types;

class CreateStructureForDataImport extends Migration
{
    protected $permissions = [
        ['name' => 'import.index', 'description' => 'Imports index', 'type' => Types::Read, 'is_default' => false],
        ['name' => 'import.store', 'description' => 'Upload file for import', 'type' => Types::Write, 'is_default' => false],
        ['name' => 'import.destroy', 'description' => 'Delete import', 'type' => Types::Write, 'is_default' => false],
        ['name' => 'import.download', 'description' => 'Download import', 'type' => Types::Read, 'is_default' => false],
        ['name' => 'import.initTable', 'description' => 'Init table for imports', 'type' => Types::Read, 'is_default' => false],
        ['name' => 'import.tableData', 'description' => 'Table data for imports', 'type' => Types::Read, 'is_default' => false],
        ['name' => 'import.exportExcel', 'description' => 'Export excel for imports', 'type' => Types::Read, 'is_default' => false],
        ['name' => 'import.downloadRejected', 'description' => 'Download rejected summary for import', 'type' => Types::Read, 'is_default' => false],
        ['name' => 'import.template', 'description' => 'Get import template', 'type' => Types::Read, 'is_default' => false],
        ['name' => 'import.uploadTemplate', 'description' => 'Upload import template', 'type' => Types::Write, 'is_default' => false],
        ['name' => 'import.deleteTemplate', 'description' => 'Delete import template', 'type' => Types::Write, 'is_default' => false],
        ['name' => 'import.downloadTemplate', 'description' => 'Download import template', 'type' => Types::Read, 'is_default' => false],
    ];

    protected $menu = [
        'name' => 'Data Import', 'icon' => 'cloud-upload-alt', 'route' => 'import.index', 'order_index' => 800, 'has_children' => false,
    ];
}
