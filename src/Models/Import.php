<?php

namespace LaravelEnso\DataImport\Models;

use Carbon\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelEnso\DataImport\Enums\Statuses;
use LaravelEnso\DataImport\Enums\Types;
use LaravelEnso\DataImport\Exceptions\Import as Exception;
use LaravelEnso\DataImport\Jobs\Import as Job;
use LaravelEnso\DataImport\Services\Template;
use LaravelEnso\DataImport\Services\Validators\Structure;
use LaravelEnso\Files\Contracts\Attachable;
use LaravelEnso\Files\Contracts\CascadesFileDeletion;
use LaravelEnso\Files\Contracts\Extensions;
use LaravelEnso\Files\Models\File;
use LaravelEnso\Files\Models\Type;
use LaravelEnso\Helpers\Casts\Obj;
use LaravelEnso\Helpers\Traits\AvoidsDeletionConflicts;
use LaravelEnso\Helpers\Traits\When;
use LaravelEnso\IO\Contracts\IOOperation;
use LaravelEnso\IO\Enums\IOTypes;
use LaravelEnso\Tables\Traits\TableCache;
use LaravelEnso\TrackWho\Traits\CreatedBy;

class Import extends Model implements
    Attachable,
    Extensions,
    IOOperation,
    CascadesFileDeletion
{
    use AvoidsDeletionConflicts, CreatedBy, HasFactory, TableCache, When;

    protected $table = 'data_imports';

    protected $guarded = [];

    protected $casts = ['status' => 'integer', 'params' => Obj::class];

    protected $template;

    public function file(): Relation
    {
        return $this->belongsTo(File::class);
    }

    public function rejected(): Relation
    {
        return $this->hasOne(RejectedImport::class);
    }

    public function chunks(): Relation
    {
        return $this->hasMany(Chunk::class, 'import_id');
    }

    public function rejectedChunks(): Relation
    {
        return $this->hasMany(RejectedChunk::class, 'import_id');
    }

    public function scopeExpired(Builder $query): Builder
    {
        $retainFor = Config::get('enso.imports.retainFor');

        if ($retainFor === 0) {
            return $query->whereId(0);
        }

        $expired = Carbon::today()->subDays($retainFor);

        return $query->where('created_at', '<', $expired);
    }

    public function extensions(): array
    {
        return ['xlsx'];
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
        return $this->batch()?->progress();
    }

    public function broadcastWith(): array
    {
        return [
            'type' => Str::lower(Types::get($this->type)),
            'filename' => $this->file?->original_name,
            'sheet' => $this->batch()?->name,
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

    public function template(): Template
    {
        return $this->template ??= new Template($this->type);
    }

    public static function cascadeFileDeletion(File $file): void
    {
        self::whereFileId($file->id)->get()->delete();
    }

    public function attach(string $savedName, string $filename): array
    {
        $path = Type::for($this::class)->path($savedName);
        $structure = new Structure($this->template(), Storage::path($path), $filename);

        if ($structure->validates()) {
            $file = File::attach($this, $savedName, $filename);
            $this->file()->associate($file)->save();

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
            $this->save();

            $file = File::upload($this, $file);
            $this->file()->associate($file)->save();

            $this->import();
        }

        return $structure->summary();
    }

    public function forceDelete()
    {
        if (! Statuses::deletable($this->status)) {
            $this->update(['status' => Statuses::Cancelled]);
        }

        $this->delete();
    }

    public function delete()
    {
        if (! Statuses::deletable($this->status)) {
            throw Exception::deleteRunningImport();
        }

        $this->rejected?->delete();
        parent::delete();
        $this->file->delete();
    }

    public function cancel()
    {
        if (! $this->running()) {
            throw Exception::cannotBeCancelled();
        }

        $this->batch()?->cancel();

        $this->update([
            'status' => Statuses::Cancelled,
            'batch' => null,
        ]);
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

        Job::dispatch($this, $sheet);
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
