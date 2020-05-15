<?php

namespace LaravelEnso\DataImport\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use LaravelEnso\DataImport\App\Enums\ImportTypes;
use LaravelEnso\DataImport\App\Enums\Statuses;
use LaravelEnso\DataImport\App\Exceptions\DataImport as DataImportException;
use LaravelEnso\DataImport\App\Jobs\Import as Job;
use LaravelEnso\DataImport\App\Services\Structure;
use LaravelEnso\DataImport\App\Services\Template;
use LaravelEnso\Files\App\Contracts\Attachable;
use LaravelEnso\Files\App\Contracts\AuthorizesFileAccess;
use LaravelEnso\Files\App\Traits\FilePolicies;
use LaravelEnso\Files\App\Traits\HasFile;
use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Helpers\App\Traits\CascadesMorphMap;
use LaravelEnso\IO\App\Contracts\IOOperation;
use LaravelEnso\IO\App\Enums\IOTypes;
use LaravelEnso\IO\App\Traits\HasIOStatuses;
use LaravelEnso\Tables\App\Traits\TableCache;
use LaravelEnso\TrackWho\App\Traits\CreatedBy;

class DataImport extends Model implements Attachable, IOOperation, AuthorizesFileAccess
{
    use CascadesMorphMap, CreatedBy, HasIOStatuses, HasFile, FilePolicies, TableCache;

    protected $extensions = ['xlsx'];

    protected $fillable = [
        'type', 'successful', 'failed', 'chunks', 'processed_chunks',
        'file_parsed', 'status', 'created_by',
    ];

    protected $casts = ['status' => 'integer', 'file_parsed' => 'boolean'];

    protected $folder = 'imports';

    public function rejected()
    {
        return $this->hasOne(RejectedImport::class);
    }

    public function handle(UploadedFile $file, array $params = [])
    {
        $template = new Template($this);
        $structure = new Structure($template, $file);

        if ($structure->isValid()) {
            tap($this)->save()
                ->upload($file);

            Job::dispatch($this, $template, $structure->sheets(), new Obj($params));
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

    public function rejectedFolder()
    {
        return $this->folder.DIRECTORY_SEPARATOR."rejected_{$this->id}";
    }

    public function delete()
    {
        if ($this->status !== Statuses::Finalized) {
            throw DataImportException::deleteRunningImport();
        }

        Storage::deleteDirectory($this->rejectedFolder());

        optional($this->rejected)->delete();

        parent::delete();
    }

    public function isFinalized()
    {
        return $this->file_parsed
            && $this->chunks === $this->processed_chunks;
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
