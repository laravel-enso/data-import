<?php

namespace LaravelEnso\DataImport\app\Models;

use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use LaravelEnso\FileManager\app\Traits\HasFile;
use LaravelEnso\FileManager\app\Contracts\Attachable;

class ImportTemplate extends Model implements Attachable
{
    use HasFile;

    protected $extensions = ['xlsx'];

    protected $fillable = ['type', 'name'];

    public function store(UploadedFile $file, $type)
    {
        $template = null;

        \DB::transaction(function () use (&$template, $file, $type) {
            $template = $this->create([
                'name' => $file->getClientOriginalName(),
                'type' => $type,
            ]);

            $template->upload($file);
        });

        return $template;
    }

    public function folder()
    {
        return config('enso.config.paths.imports');
    }
}
