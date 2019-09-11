<?php

namespace LaravelEnso\DataImport\app\Models;

use Illuminate\Http\UploadedFile;
use LaravelEnso\IO\app\Enums\IOTypes;
use Illuminate\Database\Eloquent\Model;
use LaravelEnso\Files\app\Traits\HasFile;
use LaravelEnso\IO\app\Traits\HasIOStatuses;
use LaravelEnso\IO\app\Contracts\IOOperation;
use LaravelEnso\Tables\app\Traits\TableCache;
use LaravelEnso\DataImport\app\Enums\Statuses;
use LaravelEnso\DataImport\app\Jobs\ImportJob;
use LaravelEnso\TrackWho\app\Traits\CreatedBy;
use LaravelEnso\Files\app\Contracts\Attachable;
use LaravelEnso\DataImport\app\Enums\ImportTypes;
use LaravelEnso\DataImport\app\Services\Template;
use LaravelEnso\Files\app\Contracts\AuthorizesFileAcces;
use LaravelEnso\Files\app\Traits\FilePolicies;
use LaravelEnso\DataImport\app\Services\Structure;
use LaravelEnso\DataImport\app\Exceptions\ProcessingInProgress;

class DataImport extends Model implements Attachable, IOOperation, AuthorizesFileAcces
{
    use CreatedBy, HasIOStatuses, HasFile, FilePolicies, TableCache;

    protected $extensions = ['xlsx'];

    protected $fillable = ['type', 'successful', 'failed', 'status', 'created_by'];

    protected $casts = ['status' => 'integer'];

    protected $folder = 'imports';

    public function rejected()
    {
        return $this->hasOne(RejectedImport::class);
    }

    public function handle(UploadedFile $file, array $params = [])
    {
        $template = new Template($this);
        $structure = new Structure($this, $template, $file);

        if ($structure->validates()) {
            tap($this)->save()
                ->upload($file);

            ImportJob::dispatch($this, $template, $params);
        }

        return $structure->summary()->toArray();
    }

    public function getEntriesAttribute()
    {
        return $this->entries();
    }

    public function entries()
    {
        return $this->successful + $this->failed;
    }

    public function rejectedFolder()
    {
        return $this->folder
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
}
