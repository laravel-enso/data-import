<?php

namespace LaravelEnso\DataImport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use LaravelEnso\Files\Contracts\Attachable;
use LaravelEnso\Files\Models\File;

class RejectedImport extends Model implements Attachable
{
    protected $guarded = [];

    protected $folder = 'imports';

    public function import()
    {
        return $this->belongsTo(Import::class);
    }

    public function file(): Relation
    {
        return $this->belongsTo(File::class);
    }
}
