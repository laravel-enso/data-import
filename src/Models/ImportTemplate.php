<?php

namespace LaravelEnso\DataImport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use LaravelEnso\Files\Contracts\Attachable;
use LaravelEnso\Files\Traits\HasFile;
use LaravelEnso\Helpers\Traits\CascadesMorphMap;

class ImportTemplate extends Model implements Attachable
{
    use CascadesMorphMap, HasFile;

    protected $extensions = ['xlsx'];

    protected $guarded = ['id'];

    protected $folder = 'imports';

    public function store(UploadedFile $file)
    {
        tap($this)->save()
            ->upload($file);
    }
}
