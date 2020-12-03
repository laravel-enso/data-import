<?php

namespace LaravelEnso\DataImport\Models;

use Illuminate\Bus\Batch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use LaravelEnso\DataImport\Enums\ImportTypes;
use LaravelEnso\DataImport\Enums\Statuses;
use LaravelEnso\DataImport\Exceptions\DataImport as Exception;
use LaravelEnso\DataImport\Jobs\Import;
use LaravelEnso\DataImport\Services\DTOs\Chunk;
use LaravelEnso\DataImport\Services\Template;
use LaravelEnso\DataImport\Services\Validators\Structure;
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

    protected $guarded = ['id'];

    protected $casts = [
        'status' => 'integer',
        'file_parsed' => 'boolean',
        'params' => 'array',
    ];

    protected $extensions = ['xlsx'];

    protected $folder = 'imports';

    protected $template;

    public function rejected()
    {
        return $this->hasOne(RejectedImport::class);
    }

    public function rejectedChunks()
    {
        return $this->hasMany(RejectedChunk::class);
    }

    public function batch(): Batch
    {
        return Bus::findBatch($this->batch);
    }

    public function isFinalized(): bool //TODO remove, incl file parsed & processed_chunks
    {
        return $this->file_parsed
            && $this->chunks === $this->processed_chunks;
    }

    public function name(): string
    {
        return ImportTypes::get($this->type);
    }

    public function type(): int
    {
        return IOTypes::Import;
    }

    public function waiting(): bool
    {
        return $this->status === Statuses::Waiting;
    }

    public function cancelled(): bool
    {
        return $this->status === Statuses::Cancelled;
    }

    public function processing(): bool
    {
        return $this->status === Statuses::Processing;
    }

    public function finalized(): bool
    {
        return $this->status === Statuses::Finalized;
    }

    public function template(): Template
    {
        return $this->template
            ?? $this->template = new Template($this->type);
    }

    public function attach(string $path, string $filename): array
    {
        $structure = new Structure($this->template(), Storage::path($path), $filename);

        if ($structure->validates()) {
            tap($this)->save()
                ->file->attach($path, $filename);

            $this->import();
        }

        return $structure->summary();
    }

    public function upload(UploadedFile $file): array
    {
        $path = $file->getPathname();
        $filename = $file->getClientOriginalName();
        $structure = new Structure($this->template(), $path, $filename);

        if ($structure->validates()) {
            tap($this)->save()
                ->file->upload($file);

            $this->import();
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

    public function delete()
    {
        if (! $this->finalized()) {
            throw Exception::deleteRunningImport();
        }

        optional($this->rejected)->delete();

        return parent::delete();
    }

    public function cancel()
    {
        if (! Statuses::cancellable($this->status)) {
            throw Exception::cannotBeCancelled();
        }

        $this->update(['status' => Statuses::Cancelled]);
    }

    public function updateProgress(Chunk $chunk)
    {
        $this->successful += $chunk->successful();
        $this->failed += $chunk->failed();
        $this->save();
    }

    public function import(?string $sheet = null)
    {
        if ($sheet === null) {
            $sheet = $this->template()->sheets()->first()->get('name');
        }

        Import::dispatch($this, $sheet);
    }
}
