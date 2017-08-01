<?php

namespace LaravelEnso\DataImport\app\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelEnso\Helpers\Traits\FormattedTimestamps;

class ImportTemplate extends Model
{
    use FormattedTimestamps;

    protected $fillable = [
        'type', 'original_name', 'saved_name',
    ];
}
