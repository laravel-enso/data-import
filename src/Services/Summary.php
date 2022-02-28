<?php

namespace LaravelEnso\DataImport\Services;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\Services\Obj;

class Summary
{
    private Obj $errors;

    public function __construct()
    {
        $this->errors = new Obj();
    }

    public function toArray(): array
    {
        return $this->errors->toArray();
    }

    public function errors(): Obj
    {
        return $this->errors;
    }

    public function addError(string $type, string $value): void
    {
        $this->type($this->errors, $type)->push($value);
    }

    private function type(Obj $layer, string $type): Collection
    {
        if (! $layer->has($type)) {
            $layer->set($type, new Collection());
        }

        return $layer->get($type);
    }
}
