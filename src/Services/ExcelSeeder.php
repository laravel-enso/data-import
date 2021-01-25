<?php

namespace LaravelEnso\DataImport\Services;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelEnso\DataImport\Models\DataImport;

class ExcelSeeder extends Seeder
{
    protected string $type;
    protected string $filename;
    protected string $filePath;
    protected array $params = [];

    public function run()
    {
        File::copy(
            Config::get('enso.imports.seederPath').DIRECTORY_SEPARATOR.$this->filename,
            Storage::path($this->filePath())
        );

        DataImport::factory()
            ->make(['type' => $this->type, 'params' => $this->params])
            ->attach($this->filePath(), $this->filename);
    }

    private function filePath()
    {
        return $this->filePath ??= 'imports'.DIRECTORY_SEPARATOR.Str::random(40).'.xlsx';
    }
}
