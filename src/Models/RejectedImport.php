<?php

namespace LaravelEnso\DataImport\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelEnso\Files\Contracts\Attachable;
use LaravelEnso\Files\Traits\HasFile;
use LaravelEnso\Helpers\Traits\CascadesMorphMap;

class RejectedImport extends Model implements Attachable
{
    use CascadesMorphMap, HasFile;

    protected $guarded = ['id'];

    protected $folder = 'imports';

    public function import()
    {
        return $this->belongsTo(DataImport::class);
    }
}
