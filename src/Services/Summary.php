<?php

namespace LaravelEnso\DataImport\Services;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\Services\Obj;

class Summary
{
    private string $filename;
    private Obj $errors;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
        $this->errors = new Obj();
    }

    public function toArray(): array
    {
        return [
            'filename' => $this->filename,
            'errors' => $this->errors,
        ];
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
