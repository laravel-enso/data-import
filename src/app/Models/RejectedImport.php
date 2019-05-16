<?php

namespace LaravelEnso\DataImport\app\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelEnso\Files\app\Traits\HasFile;
use LaravelEnso\Files\app\Contracts\Attachable;

class RejectedImport extends Model implements Attachable
{
    use HasFile;

    protected $fillable = ['data_import_id'];

    public function dataImport()
    {
        return $this->belongsTo(DataImport::class);
    }

    public function folder(): string
    {
        return 'imports'
            .DIRECTORY_SEPARATOR
            .'rejected_'.$this->data_import_id;
    }
}
