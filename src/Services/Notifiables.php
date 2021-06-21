<?php

namespace LaravelEnso\DataImport\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use LaravelEnso\DataImport\Models\DataImport;
use LaravelEnso\Users\Models\User;

class Notifiables
{
    public static function get(DataImport $import): Collection
    {
        $ids = explode(',', Config::get('enso.imports.notifiableIds'));

        return User::whereIn('id', $ids)
            ->where('id', '<>', $import->file->createdBy->id)
            ->get();
    }
}
