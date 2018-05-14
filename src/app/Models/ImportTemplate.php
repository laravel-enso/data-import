<?php

namespace LaravelEnso\DataImport\app\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelEnso\DataImport\app\Classes\Handlers\Storer;
use LaravelEnso\DataImport\app\Classes\Handlers\Presenter;

class ImportTemplate extends Model
{
    protected $fillable = ['type', 'original_name', 'saved_name'];

    public function download()
    {
        return (new Presenter($this))
            ->download();
    }

    public static function store(array $request, $type)
    {
        return (new Storer($request, $type))
            ->run();
    }
}
