<?php

namespace LaravelEnso\DataImport\Models;

use Carbon\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelEnso\DataImport\Enums\Statuses;
use LaravelEnso\DataImport\Enums\Types;
use LaravelEnso\DataImport\Exceptions\DataImport as Exception;
use LaravelEnso\DataImport\Jobs\Import;
use LaravelEnso\DataImport\Services\Template;
use LaravelEnso\DataImport\Services\Validators\Structure;
use LaravelEnso\Files\Contracts\Attachable;
use LaravelEnso\Files\Contracts\AuthorizesFileAccess;
use LaravelEnso\Files\Traits\FilePolicies;
use LaravelEnso\Files\Traits\HasFile;
use LaravelEnso\Helpers\Traits\CascadesMorphMap;
use LaravelEnso\Helpers\Traits\When;
use LaravelEnso\IO\Contracts\IOOperation;
use LaravelEnso\IO\Enums\IOTypes;
use LaravelEnso\Tables\Traits\TableCache;
use LaravelEnso\TrackWho\Traits\CreatedBy;

class DataImport extends Model implements Attachable, IOOperation, AuthorizesFileAccess
{
    use CascadesMorphMap, CreatedBy, HasFactory, HasFile, FilePolicies, TableCache, When;

    protected $guarded = [];

    protected $casts = ['status' => 'integer', 'params' => 'array'];

    protected $extensions = ['xlsx'];

    protected $folder = 'imports';

    protected $template;

    public function rejected()
    {
        return $this->hasOne(RejectedImport::class);
    }

    public function chunks()
    {
        return $this->hasMany(Chunk::class, 'import_id');
    }

    public function rejectedChunks()
    {
        return $this->hasMany(RejectedChunk::class, 'import_id');
    }

    public function batch(): ?Batch
    {
        return $this->batch ? Bus::findBatch($this->batch) : null;
    }

    public function getEntriesAttribute()
    {
        return $this->entries();
    }

    public function entries()
    {
        return $this->successful + $this->failed;
    }

    public function type(): string
    {
        return Types::get($this->type);
    }

    public function operationType(): int
    {
        return IOTypes::Import;
    }

    public function progress(): ?int
    {
        return optional($this->batch())->progress();
    }

    public function broadcastWith(): array
    {
        return [
            'type' => Str::lower(Types::get($this->type)),
            'filename' => $this->file->original_name,
            'sheet' => optional($this->batch())->name,
            'successful' => $this->successful,
            'failed' => $this->failed,
        ];
    }

    public function createdAt(): Carbon
    {
        return $this->created_at;
    }

    public function status(): int
    {
        return $this->running()
            ? $this->status
            : Statuses::Finalized;
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

    public function running(): bool
    {
        return in_array($this->status, Statuses::running());
    }

    public function deletable(): bool
    {
        return in_array($this->status, Statuses::deletable());
    }

    public function template(): Template
    {
        return $this->template ??= new Template($this->type);
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

    public function delete()
    {
        if (! $this->deletable()) {
            throw Exception::deleteRunningImport();
        }

        optional($this->rejected)->delete();

        return parent::delete();
    }

    public function cancel()
    {
        if (! $this->running()) {
            throw Exception::cannotBeCancelled();
        }

        optional($this->batch())->cancel();

        $this->update(['status' => Statuses::Cancelled]);
    }

    public function updateProgress(int $successful, int $failed)
    {
        $this->successful += $successful;
        $this->failed += $failed;
        $this->save();
    }

    public function import(?string $sheet = null)
    {
        if ($sheet === null) {
            $sheet = $this->template()->sheets()->first()->get('name');
        }

        Import::dispatch($this, $sheet);
    }

    public function restart(): self
    {
        $this->update([
            'successful' => 0,
            'failed' => 0,
            'status' => Statuses::Waiting,
        ]);

        return $this;
    }
}
