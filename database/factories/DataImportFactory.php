<?php

namespace LaravelEnso\DataImport\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use LaravelEnso\DataImport\Enums\Statuses;
use LaravelEnso\DataImport\Models\DataImport;

class DataImportFactory extends Factory
{
    protected $model = DataImport::class;

    public function definition()
    {
        return [
            'type' => $this->type(),
            'params' => [],
            'successful' => 0,
            'failed' => 0,
            'chunks' => 0,
            'processed_chunks' => 0,
            'file_parsed' => false,
            'status' => Statuses::values()->random(),
        ];
    }

    private function type(): string
    {
        return Collection::wrap(Config::get('enso.imports.configs'))
            ->keys()->random();
    }
}
