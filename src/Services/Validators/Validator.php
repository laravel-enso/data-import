<?php

namespace LaravelEnso\DataImport\Services\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\Core\Models\User;
use LaravelEnso\Helpers\Services\Obj;

abstract class Validator
{
    private Collection $errors;

    public function __construct()
    {
        $this->errors = new Collection();
    }

    abstract public function run(Obj $row, User $user, Obj $params);

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
