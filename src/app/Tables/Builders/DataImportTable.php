<?php

namespace LaravelEnso\DataImport\app\Tables\Builders;

use LaravelEnso\VueDatatable\app\Classes\Table;
use LaravelEnso\DataImport\app\Models\DataImport;

class DataImportTable extends Table
{
    protected $templatePath = __DIR__.'/../Templates/dataImports.json';

    public function query()
    {
        return DataImport::select(\DB::raw('data_imports.id, data_imports.id as "dtRowId",
                data_imports.type, files.original_name as name, data_imports.created_at,
                people.name as createdBy'))
            ->join('files', function ($join) {
                $join->on('attachable_id', 'data_imports.id')
                    ->where('attachable_type', DataImport::class);
            })->join('users', 'files.created_by', '=', 'users.id')
            ->join('people', 'users.person_id', '=', 'people.id');
    }
}
