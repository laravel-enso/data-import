<?php

namespace LaravelEnso\DataImport\App\Services\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\Core\App\Models\User;
use LaravelEnso\Helpers\App\Classes\Obj;

abstract class Validator
{
    private Collection $errors;

    public function __construct()
    {
        $this->errors = new Collection();
    }

    abstract public function run(Obj $row, User $user, Obj $params);

    public function fails(): bool
    {
        return $this->errors->isNotEmpty();
    }

    public function message(): string
    {
        return $this->errors->implode(' | ');
    }

    public function addError(string $error): void
    {
        $this->errors->push($error);
    }

    public function clearErrors(): void
    {
        $this->errors->splice(0);
    }

    public function user(): User
    {
        return $this->user;
    }

    public function params(): Obj
    {
        return $this->params;
    }
}
