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
        File::copy($this->source(), $this->hash());

        DataImport::factory()
            ->make(['type' => $this->type, 'params' => $this->params])
            ->attach($this->path(), $this->filename);
    }

    private function source(): string
    {
        return Config::get('enso.imports.seederPath').'/'.$this->filename;
    }

    private function hash(): string
    {
        return Storage::path($this->path());
    }

    private function path(): string
    {
        return $this->filePath ??= 'imports'.'/'.Str::random(40).'.xlsx';
    }
}
