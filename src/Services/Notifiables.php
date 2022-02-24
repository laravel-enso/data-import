<?php

namespace LaravelEnso\DataImport\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use LaravelEnso\DataImport\Models\Import;
use LaravelEnso\Users\Models\User;

class Notifiables
{
    public static function get(Import $import): Collection
    {
        $ids = explode(',', Config::get('enso.imports.notifiableIds'));

        return User::whereIn('id', $ids)
            ->where('id', '<>', $import->file->createdBy->id)
            ->get();
    }
}
