<?php

namespace LaravelEnso\DataImport\Services\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\DataImport\Models\Import;
use LaravelEnso\Helpers\Services\Obj;

abstract class Validator
{
    private Collection $errors;

    public function __construct()
    {
        $this->errors = new Collection();
    }

    abstract public function run(Obj $row, Import $import);

    public function errors(): Collection
    {
        return $this->errors;
    }

    public function fails(): bool
    {
        return $this->errors->isNotEmpty();
    }

    public function addError(string $error): void
    {
        $this->errors->push($error);
    }
}
