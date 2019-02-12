<?php

namespace LaravelEnso\DataImport\app\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelEnso\FileManager\app\Traits\HasFile;
use LaravelEnso\FileManager\app\Contracts\Attachable;
use LaravelEnso\FileManager\app\Contracts\VisibleFile;

class RejectedImport extends Model implements Attachable, VisibleFile
{
    use HasFile;

    protected $fillable = ['data_import_id'];

    public function dataImport()
    {
        return $this->belogsTo(DataImport::class);
    }

    public function folder()
    {
        return config('enso.config.paths.imports')
            .DIRECTORY_SEPARATOR
            .'rejected_'.$this->data_import_id;
    }

    public function isDeletable()
    {
        return false;
    }
}
