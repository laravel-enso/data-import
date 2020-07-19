<?php

namespace LaravelEnso\DataImport\Contracts;

use Illuminate\Support\Collection;

interface FieldValidation
{
    public function mandatory(): Collection;

    public function optional(): Collection;

    public function dependent(?string $type): Collection;
}
