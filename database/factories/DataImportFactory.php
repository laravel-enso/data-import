<?php

namespace LaravelEnso\DataImport\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelEnso\DataImport\Enums\Status;
use LaravelEnso\DataImport\Models\DataImport;

class DataImportFactory extends Factory
{
    protected $model = DataImport::class;

    public function definition()
    {
        return [
            'type' => null,
            'batch' => null,
            'params' => [],
            'successful' => 0,
            'failed' => 0,
            'status' => Status::Waiting->value,
        ];
    }
}
