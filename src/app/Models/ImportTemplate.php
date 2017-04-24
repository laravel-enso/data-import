<?php

namespace LaravelEnso\DataImport\app\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ImportTemplate extends Model
{
    protected $fillable = ['type', 'original_name', 'saved_name'];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }
}
