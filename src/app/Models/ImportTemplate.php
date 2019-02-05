<?php

namespace LaravelEnso\DataImport\app\Models;

use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use LaravelEnso\FileManager\app\Traits\HasFile;
use LaravelEnso\FileManager\app\Contracts\Attachable;
use LaravelEnso\Multitenancy\app\Traits\MixedConnection;
use LaravelEnso\Multitenancy\app\Traits\ConnectionStoragePath;

class ImportTemplate extends Model implements Attachable
{
    use ConnectionStoragePath, HasFile, MixedConnection;

    protected $extensions = ['xlsx'];

    protected $fillable = ['type'];

    public function store(UploadedFile $file)
    {
        tap($this)->save()
            ->upload($file);
    }

    public function folder()
    {
        return $this->storagePath('imports');
    }
}
