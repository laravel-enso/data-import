<?php

namespace LaravelEnso\DataImport\Services;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use LaravelEnso\DataImport\Models\DataImport;

class ExcelSeeder extends Seeder
{
    protected string $type;
    protected string $filename;
    protected array $params = [];

    public function run()
    {
        DataImport::factory()
            ->make(['type' => $this->type, 'params' => $this->params])
            ->attach(Config::get('enso.imports.seederPath'), $this->filename);
    }
}
