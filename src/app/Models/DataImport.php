<?php

namespace LaravelEnso\DataImport\app\Models;

use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use LaravelEnso\Core\app\Enums\IOTypes;
use LaravelEnso\DataImport\app\Enums\Statuses;
use LaravelEnso\DataImport\app\Jobs\ImportJob;
use LaravelEnso\TrackWho\app\Traits\CreatedBy;
use LaravelEnso\Core\app\Contracts\IOOperation;
use LaravelEnso\FileManager\app\Traits\HasFile;
use LaravelEnso\DataImport\app\Classes\Template;
use LaravelEnso\DataImport\app\Classes\Structure;
use LaravelEnso\DataImport\app\Enums\ImportTypes;
use LaravelEnso\ActivityLog\app\Traits\LogsActivity;
use LaravelEnso\FileManager\app\Contracts\Attachable;
use LaravelEnso\FileManager\app\Contracts\VisibleFile;
use LaravelEnso\DataImport\app\Exceptions\ProcessingInProgress;

class DataImport extends Model implements Attachable, VisibleFile, IOOperation
{
    use CreatedBy, HasFile, LogsActivity;

    protected $extensions = ['xlsx'];

    protected $fillable = ['type', 'successful', 'failed', 'status'];

    protected $casts = ['status' => 'integer'];

    protected $loggableLabel = 'type';

    protected $loggable = [];

    public function rejected()
    {
        return $this->hasOne(RejectedImport::class);
    }

    public function run(UploadedFile $file, array $params = [])
    {
        $template = new Template($this);
        $structure = new Structure($this, $template, $file);

        if ($structure->validates()) {
            $this->status = Statuses::Waiting;

            tap($this)->save()
                ->upload($file);

            ImportJob::dispatch($this, $template, $params);
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

    public function delete()
    {
        if ($this->status !== Statuses::Finalized) {
            throw new ProcessingInProgress(
                __('The import is currently running and cannot be deleted')
            );
        }

        \Storage::deleteDirectory($this->rejectedFolder());

        parent::delete();
    }

    public function name()
    {
        return ImportTypes::get($this->type);
    }

    public function type()
    {
        return IOTypes::Import;
    }

    public function status()
    {
        return $this->status;
    }
}
