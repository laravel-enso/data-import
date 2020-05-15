<?php

namespace LaravelEnso\DataImport\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use LaravelEnso\Files\App\Contracts\Attachable;
use LaravelEnso\Files\App\Traits\HasFile;
use LaravelEnso\Helpers\App\Traits\CascadesMorphMap;

class ImportTemplate extends Model implements Attachable
{
    use CascadesMorphMap, HasFile;

    protected $extensions = ['xlsx'];

    protected $fillable = ['type'];

    protected $folder = 'imports';

    public function store(UploadedFile $file)
    {
        tap($this)->save()
            ->upload($file);
    }
}
