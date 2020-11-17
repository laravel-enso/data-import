<?php

namespace LaravelEnso\DataImport\Upgrades;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use LaravelEnso\Files\Models\File;
use LaravelEnso\Upgrade\Contracts\MigratesTable;

class DropImportTemplateTable implements MigratesTable
{
    public function isMigrated(): bool
    {
        return ! Schema::hasTable('import_templates');
    }

    public function migrateTable(): void
    {
        Storage::disk(Config::get('filesystems.default'))
            ->delete($this->files());

        File::whereAttachableType('importTemplate')->delete();

        Schema::dropIfExists('import_templates');
    }

    private function files(): array
    {
        return File::whereAttachableType('importTemplate')->get()
            ->map(fn (File $file) => $this->path($file))
            ->toArray();
    }

    private function path(File $file): string
    {
        return 'imports'.DIRECTORY_SEPARATOR.$file->saved_name;
    }
}
