<?php

namespace LaravelEnso\DataImport\App\Http\Controllers;

use App\Http\Controllers\Controller;
use LaravelEnso\DataImport\app\DataTable\ImportTableStructure;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataTable\app\Traits\DataTable;

class DataImportTableController extends Controller
{
    use DataTable;

    protected $tableStructureClass = ImportTableStructure::class;

    public function getTableQuery()
    {
        return DataImport::select(\DB::raw('data_imports.id, data_imports.id as DT_RowId, data_imports.type,
                data_imports.original_name, data_imports.comment, data_imports.created_at,
                concat(users.first_name, " ", users.last_name) as created_by'))
            ->join('users', 'data_imports.created_by', '=', 'users.id');
    }
}
