<?php

namespace LaravelEnso\DataImport\Services;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelEnso\DataImport\Models\Import;
use LaravelEnso\Files\Models\Type;
use LaravelEnso\Users\Models\User;

abstract class ExcelSeeder extends Seeder
{
    protected string $savedName;

    public function __construct()
    {
        $this->savedName = $this->hashname();
    }

    public function run(): array
    {
        File::copy($this->source(), Storage::path($this->path()));

        Auth::setUser($this->user());

        return Import::factory()
            ->make(['type' => $this->type(), 'params' => $this->params()])
            ->attach($this->savedName, $this->filename());
    }

    abstract protected function type(): string;

    abstract protected function filename(): string;

    protected function user(): User
    {
        return User::find(Config::get('enso.imports.seederUserId'));
    }

    protected function params(): array
    {
        return [];
    }

    protected function hashname(): string
    {
        $hash = Str::random(40);

        return "{$hash}.xlsx";
    }

    private function source(): string
    {
        $path = Config::get('enso.imports.seederPath');

        return "{$path}/{$this->filename()}";
    }

    private function path(): string
    {
        return Type::for(Import::class)->path($this->savedName);
    }
}
