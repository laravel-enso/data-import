<?php

namespace LaravelEnso\DataImport\app\Models;

use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use LaravelEnso\DataImport\app\Enums\Statuses;
use LaravelEnso\DataImport\app\Jobs\ImportJob;
use LaravelEnso\FileManager\app\Traits\HasFile;
use LaravelEnso\DataImport\app\Classes\Structure;
use LaravelEnso\ActivityLog\app\Traits\LogsActivity;
use LaravelEnso\FileManager\app\Contracts\Attachable;
use LaravelEnso\FileManager\app\Contracts\VisibleFile;

class DataImport extends Model implements Attachable, VisibleFile
{
    use HasFile, LogsActivity;

    protected $extensions = ['xlsx'];

    protected $fillable = ['type', 'successful', 'failed', 'status'];

    protected $loggableLabel = 'type';

    protected $loggable = [];

    private $importFile;

    protected static function boot()
    {
        parent::boot();

        self::deleting(function ($model) {
            \Storage::deleteDirectory($model->rejectedFolder());
        });
    }

    public function rejected()
    {
        return $this->hasOne(RejectedImport::class);
    }

    public function run(UploadedFile $file)
    {
        $structure = new Structure($this, $file);

        if ($structure->validates()) {
            $this->status = Statuses::Waiting;

            tap($this)->save()
                ->upload($file);

            ImportJob::dispatch($this);
        }

        return $structure->summary();
    }

    public function getEntriesAttribute()
    {
        return $this->entries();
    }

    public function entries()
    {
        return $this->successful + $this->failed;
    }

    public function folder()
    {
        return config('enso.config.paths.imports');
    }

    public function isDeletable()
    {
        return true;
    }

    public function rejectedFolder()
    {
        return config('enso.config.paths.imports')
            .DIRECTORY_SEPARATOR
            .'rejected_'.$this->id;
    }
}
