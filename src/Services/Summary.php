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

    public function addError(string $category, string $value): void
    {
        $this->category($this->errors, $category)->push($value);
    }

    private function category(Obj $container, string $category): Collection
    {
        if (! $container->has($category)) {
            $container->set($category, new Collection());
        }

        return $container->get($category);
    }
}
