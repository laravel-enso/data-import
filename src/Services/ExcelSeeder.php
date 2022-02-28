<?php

namespace LaravelEnso\DataImport\Services;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelEnso\DataImport\Models\Import;
use LaravelEnso\Files\Models\Type;

abstract class ExcelSeeder extends Seeder
{
    protected string $type;
    protected string $filename;
    protected string $filePath;
    protected array $params = [];

    public function __construct()
    {
        $this->savedName = "{$this->hash()}.xlsx";
    }

    public function run()
    {
        File::copy($this->source(), Storage::path($this->path()));

        Import::factory()
            ->make(['type' => $this->type, 'params' => $this->params])
            ->attach($this->savedName, $this->filename);
    }

    abstract protected function type(): string;

    abstract protected function filename(): string;

    protected function params(): array
    {
        return [];
    }

    private function source(): string
    {
        $path = Config::get('enso.imports.seederPath');

        return "{$path}/{$this->filename}";
    }

    private function path(): string
    {
        return Type::for(Import::class)->path($this->savedName);
    }

    private function hash(): string
    {
        return Str::random(40);
    }
}
