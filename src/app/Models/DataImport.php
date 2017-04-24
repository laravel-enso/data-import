<?php

namespace LaravelEnso\DataImport\app\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use LaravelEnso\TrackWho\app\Traits\CreatedBy;

class DataImport extends Model
{
    use CreatedBy;

    protected $fillable = ['type', 'original_name', 'saved_name', 'comment', 'summary'];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }
}
