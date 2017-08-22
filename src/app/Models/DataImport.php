<?php

namespace LaravelEnso\DataImport\app\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelEnso\Core\app\Models\Preference;
use LaravelEnso\Helpers\Traits\FormattedTimestamps;
use LaravelEnso\TrackWho\app\Traits\CreatedBy;

class DataImport extends Model
{
    use CreatedBy, FormattedTimestamps;

    protected $fillable = [
        'type', 'original_name', 'saved_name', 'comment', 'summary',
    ];

    protected $casts = ['summary' => 'object'];

    public function getSuccessfulAttribute()
    {
    	$import = DataImport::find($this->id);

    	return $import->summary->successful;
    }

    public function getErrorsAttribute()
    {
    	$import = DataImport::find($this->id);

    	return $import->summary->errors;
    }
}
