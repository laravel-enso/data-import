<?php

namespace LaravelEnso\DataImport\app\Tables\Builders;

use LaravelEnso\VueDatatable\app\Classes\Table;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Models\RejectedImportSummary;

class DataImportTable extends Table
{
    protected $templatePath = __DIR__.'/../Templates/dataImports.json';

    public function query()
    {
        return DataImport::select(\DB::raw('
            data_imports.id, data_imports.id as "dtRowId", data_imports.type, data_imports.status,
            data_imports.status as computedStatus, files.original_name as name,
            data_imports.successful, data_imports.failed, data_imports.created_at,
            people.name as createdBy, rejected_imports.id as rejectedId
        '))->join('files', function ($join) {
            $join->on('files.attachable_id', 'data_imports.id')
                ->where('files.attachable_type', DataImport::class);
        })->join('users', 'files.created_by', '=', 'users.id')
        ->join('people', 'users.person_id', '=', 'people.id')
        ->leftJoin('rejected_imports', 'data_imports.id', '=', 'rejected_imports.data_import_id')
        ->leftJoin('files as rejected_files', function ($join) {
            $join->on('rejected_files.attachable_id', 'rejected_imports.id')
            ->where('rejected_files.attachable_type', RejectedImportSummary::class);
        });
    }
}
