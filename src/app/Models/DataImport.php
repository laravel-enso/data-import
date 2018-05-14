<?php

namespace LaravelEnso\DataImport\app\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelEnso\TrackWho\app\Traits\CreatedBy;
use LaravelEnso\DataImport\app\Classes\Handlers\Importer;
use LaravelEnso\DataImport\app\Classes\Handlers\Presenter;

class DataImport extends Model
{
    use CreatedBy;

    protected $fillable = ['type', 'original_name', 'saved_name', 'comment', 'summary'];

    protected $casts = ['summary' => 'object'];

    public function getSuccessfulAttribute()
    {
        $import = self::find($this->id);

        return $import->summary->successful;
    }

    public function getIssuesAttribute()
    {
        $import = self::find($this->id);

        return collect($import->summary->contentIssues)->count();
    }

    public function download()
    {
        return (new Presenter($this))
            ->download();
    }

    public function summary()
    {
        return json_encode($this->summary);
    }

    public static function store(array $request, $type)
    {
        return (new Importer($request, $type))
            ->run();
    }
}
