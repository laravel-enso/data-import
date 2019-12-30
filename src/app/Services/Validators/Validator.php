<?php

namespace LaravelEnso\DataImport\App\Services\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\Core\App\Models\User;
use LaravelEnso\Helpers\App\Classes\Obj;

abstract class Validator
{
    protected array $rules;

    private Collection $errors;
    private Obj $params;

    public function __construct(array $rules, User $user, Obj $params)
    {
        $this->rules = $rules;
        $this->user = $user;
        $this->params = $params;
        $this->errors = new Collection();
    }

    abstract public function run(Obj $row);

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
