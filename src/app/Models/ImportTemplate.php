<?php

namespace LaravelEnso\DataImport\app\Models;

use Illuminate\Database\Eloquent\Model;

class ImportTemplate extends Model
{
    protected $fillable = ['type', 'original_name', 'saved_name'];
}
