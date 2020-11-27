<?php

namespace LaravelEnso\DataImport\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use LaravelEnso\DataImport\Enums\ImportTypes;
use LaravelEnso\DataImport\Enums\Statuses;
use LaravelEnso\DataImport\Exceptions\DataImport as DataImportException;
use LaravelEnso\Files\Contracts\Attachable;
use LaravelEnso\Files\Contracts\AuthorizesFileAccess;
use LaravelEnso\Files\Traits\FilePolicies;
use LaravelEnso\Files\Traits\HasFile;
use LaravelEnso\Helpers\Traits\CascadesMorphMap;
use LaravelEnso\IO\Contracts\IOOperation;
use LaravelEnso\IO\Enums\IOTypes;
use LaravelEnso\IO\Traits\HasIOStatuses;
use LaravelEnso\Tables\Traits\TableCache;
use LaravelEnso\TrackWho\Traits\CreatedBy;

class DataImport extends Model implements Attachable, IOOperation, AuthorizesFileAccess
{
    use CascadesMorphMap,
        CreatedBy,
        HasIOStatuses,
        HasFactory,
        HasFile,
        FilePolicies,
        TableCache;

    protected $extensions = ['xlsx'];

    protected $guarded = ['id'];

    protected $casts = [
        'status' => 'integer',
        'file_parsed' => 'boolean',
        'params' => 'array',
    ];

    protected $folder = 'imports';

    public function rejected()
    {
        return $this->hasOne(RejectedImport::class);
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

        return parent::delete();
    }

    public function cancel()
    {
        throw_unless(Statuses::cancelable($this->status), DataImportException::cannotBeCanceled());

        $this->update(['status' => Statuses::Canceled]);
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
