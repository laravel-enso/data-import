<?php

namespace LaravelEnso\DataImport\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelEnso\DataImport\Models\RejectedChunk;

class RejectedChunkFactory extends Factory
{
    protected $model = RejectedChunk::class;

    public function definition()
    {
        return [
            'data_import_id' => null,
            'sheet' => $this->faker->name,
            'header' => [],
            'content' => [],
        ];
    }
}
