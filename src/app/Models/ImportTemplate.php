<?php

namespace LaravelEnso\DataImport\app\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelEnso\FileManager\app\Traits\HasFile;
use LaravelEnso\FileManager\app\Contracts\Attachable;

class ImportTemplate extends Model implements Attachable
{
    use HasFile;

    protected $extensions = ['xlsx'];

    protected $fillable = ['type'];

    public function folder()
    {
        return config('enso.config.paths.imports');
    }
}
