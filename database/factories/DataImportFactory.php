<?php

namespace LaravelEnso\DataImport\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use LaravelEnso\DataImport\Models\DataImport;

class DataImportFactory extends Factory
{
    protected $model = DataImport::class;

    public function definition()
    {
        return [
            'type' => (new Collection(config('enso.imports.configs')))->keys()->random(),
            'successful' => 0,
            'failed' => 0,
            'chunks' => 0,
            'processed_chunks' => 0,
            'file_parsed' => false,
        ];
    }
}
