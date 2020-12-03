<?php

namespace LaravelEnso\DataImport\Services\DTOs;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use LaravelEnso\Helpers\Services\Obj;

class Row
{
    private Obj $content;
    private Collection $errors;

    public function __construct($items = [])
    {
        $this->content = new Obj($items);
        $this->errors = new Collection();
    }

    public function content(): Obj
    {
        return $this->content;
    }

    public function errors(): Collection
    {
        return $this->errors;
    }

    public function valid(): bool
    {
        return $this->errors->isEmpty();
    }

    public function unknownError(): void
    {
        $this->errors->push(Config::get('enso.imports.unknownError'));
    }
}
