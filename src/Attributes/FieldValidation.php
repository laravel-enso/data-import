<?php

namespace LaravelEnso\DataImport\Attributes;

use Illuminate\Support\Collection;

trait FieldValidation
{
    public function mandatory(): Collection
    {
        return new Collection(static::Mandatory);
    }

    public function optional(): Collection
    {
        return new Collection(static::Optional);
    }

    public function dependent($type): Collection
    {
        return new Collection(static::Dependent[$type] ?? []);
    }
}
