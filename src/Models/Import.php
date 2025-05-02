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
use Illuminate\Support\Traits\Conditionable;
use LaravelEnso\DataImport\Enums\Status;
use LaravelEnso\DataImport\Exceptions\Import as Exception;
use LaravelEnso\DataImport\Jobs\Import as Job;
use LaravelEnso\DataImport\Services\Options;
use LaravelEnso\DataImport\Services\Template;
use LaravelEnso\DataImport\Services\Validators\Structure;
use LaravelEnso\Files\Contracts\Attachable;
use LaravelEnso\Files\Contracts\CascadesFileDeletion;
use LaravelEnso\Files\Contracts\Extensions;
use LaravelEnso\Files\Models\File;
use LaravelEnso\Files\Models\Type;
use LaravelEnso\Helpers\Casts\Obj;
use LaravelEnso\Helpers\Traits\AvoidsDeletionConflicts;
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
    use AvoidsDeletionConflicts, CreatedBy, HasFactory, TableCache, Conditionable;

    protected $table = 'data_imports';

    protected $guarded = [];

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

    public function scopeStuck(Builder $query): Builder
    {
        $cancelStuckAfter = Carbon::today()
            ->subHours(Config::get('enso.imports.cancelStuckAfter'));

        return $query->where('created_at', '<', $cancelStuckAfter)
            ->whereNotIn('status', [Status::Finalized->value, Status::Cancelled->value]);
    }

    public function scopeDeletable(Builder $query): Builder
    {
        return $query->whereIn('status', Status::deletable());
    }

    public function scopeNotDeletable(Builder $query): Builder
    {
        return $query->whereNotIn('status', Status::deletable());
    }

    public function extensions(): array
    {
        return ['xlsx', 'csv', 'txt'];
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
        return Options::label($this->type);
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
        $label = Config::get('enso.imports')['configs'][$this->type]['label'];

        return [
            'type' => Str::lower($label),
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
            : Status::Finalized->value;
    }

    public function waiting(): bool
    {
        return $this->status === Status::Waiting->value;
    }

    public function cancelled(): bool
    {
        return $this->status === Status::Cancelled->value;
    }

    public function processing(): bool
    {
        return $this->status === Status::Processing->value;
    }

    public function finalized(): bool
    {
        return $this->status === Status::Finalized->value;
    }

    public function running(): bool
    {
        return Status::isRunning($this->status);
    }

    public function template(): Template
    {
        return $this->template ??= new Template($this->type);
    }

    public static function cascadeFileDeletion(File $file): void
    {
        self::whereFileId($file->id)->first()->delete();
    }

    public function attach(string $savedName, string $filename): array
    {
        $path = Type::for($this::class)->path($savedName);
        $extension = Str::afterLast($filename, '.');
        $args = [$this->template(), Storage::path($path), $filename, $extension];

        $structure = new Structure(...$args);

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
        $extension = $file->getClientOriginalExtension();

        $args = [$this->template(), $path, $filename, $extension];

        $structure = new Structure(...$args);

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
        if (! Status::isDeletable($this->status)) {
            $this->update(['status' => Status::Cancelled->value]);
        }

        $this->delete();
    }

    public function purge(): void
    {
        $this->rejected?->delete();
        $file = $this->file;
        $this->file()->dissociate()->save();
        $file?->delete();
    }

    public function delete()
    {
        if (! Status::isDeletable($this->status)) {
            throw Exception::deleteRunningImport();
        }

        $this->rejected?->delete();

        $response = parent::delete();

        $this->file?->delete();

        return $response;
    }

    public function cancel()
    {
        if (! $this->running()) {
            throw Exception::cannotBeCancelled();
        }

        $this->batch()?->cancel();

        $this->update([
            'status' => Status::Cancelled->value,
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

    public function restart(): void
    {
        $this->rejected?->delete();

        $this->update([
            'successful' => 0,
            'failed' => 0,
            'status' => Status::Waiting->value,
        ]);

        $this->import();
    }

    protected function casts(): array
    {
        return [
            'status' => 'integer', 'params' => Obj::class,
        ];
    }
}
