<?php

namespace LaravelEnso\DataImport\App\Http\Controllers;

use App\Http\Controllers\Controller;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\VueDatatable\app\Traits\Datatable;
use LaravelEnso\VueDatatable\app\Traits\Excel;

class DataImportTableController extends Controller
{
    use Datatable, Excel;

    private const Template = __DIR__.'/../../Tables/dataImports.json';

    public function query()
    {
        return DataImport::select(\DB::raw('data_imports.id, data_imports.id as dtRowId, data_imports.type,
                data_imports.original_name, data_imports.comment, data_imports.created_at,
                concat(users.first_name, " ", users.last_name) as created_by'))
            ->join('users', 'data_imports.created_by', '=', 'users.id');
    }

    use DataTable;
}
