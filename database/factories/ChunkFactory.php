<?php

namespace LaravelEnso\DataImport\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelEnso\DataImport\Models\Chunk;

class ChunkFactory extends Factory
{
    protected $model = Chunk::class;

    public function definition()
    {
        return [
            'import_id' => null,
            'sheet' => $this->faker->name,
            'header' => [],
            'rows' => [],
        ];
    }
}
