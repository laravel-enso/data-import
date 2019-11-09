<?php

namespace LaravelEnso\DataImport\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use LaravelEnso\Files\app\Contracts\Attachable;
use LaravelEnso\Files\app\Traits\HasFile;

class ImportTemplate extends Model implements Attachable
{
    use HasFile;

    protected $extensions = ['xlsx'];

    protected $fillable = ['type'];

    protected $folder = 'imports';

    public function store(UploadedFile $file)
    {
        tap($this)->save()
            ->upload($file);
    }
}
