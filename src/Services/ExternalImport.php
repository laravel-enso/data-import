<?php

namespace LaravelEnso\DataImport\Services;

use Illuminate\Support\Str;
use LaravelEnso\DataImport\Enums\Statuses;
use LaravelEnso\DataImport\Jobs\Finalize;
use LaravelEnso\DataImport\Jobs\RejectedExport;
use LaravelEnso\DataImport\Models\Import;
use Throwable;

class ExternalImport
{
    public function __construct(
        private Import $import,
        private ?string $label = null,
    ) {
    }

    public function finalize(): bool
    {
        if (! $this->claim()) {
            return false;
        }

        try {
            RejectedExport::withChain([
                new Finalize(
                    $this->import,
                    false,
                    $this->label ?? Str::headline($this->import->type),
                ),
            ])->dispatch($this->import, false);
        } catch (Throwable $throwable) {
            $this->release();

            throw $throwable;
        }

        return true;
    }

    private function claim(): bool
    {
        return $this->import->newQuery()
            ->whereKey($this->import->id)
            ->where('status', Statuses::Processed)
            ->update(['status' => Statuses::ExportingRejected]) === 1;
    }

    private function release(): void
    {
        $this->import->newQuery()
            ->whereKey($this->import->id)
            ->where('status', Statuses::ExportingRejected)
            ->update(['status' => Statuses::Processed]);
    }
}
