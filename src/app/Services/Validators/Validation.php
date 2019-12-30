<?php

namespace LaravelEnso\DataImport\App\Services\Validators;

use LaravelEnso\Core\App\Models\User;
use LaravelEnso\DataImport\App\Services\Validators\Row as ImplicitValidator;
use LaravelEnso\Helpers\App\Classes\Obj;

class Validation
{
    private Obj $row;
    private array $rules;
    private ?Validator $custom;
    private User $user;
    private Obj $params;
    private $errorColumn;

    public function __construct(Obj $row, array $rules, ?Validator $custom, User $user, Obj $params)
    {
        $this->row = $row;
        $this->rules = $rules;
        $this->custom = $custom;
        $this->user = $user;
        $this->params = $params;
        $this->errorColumn = config('enso.imports.errorColumn');
    }

    public function run(): void
    {
        $this->validator($this->implicit())
            ->validator($this->custom);
    }

    private function validator($validator): self
    {
        if (! $validator) {
            return $this;
        }

        $validator->run($this->row);

        if ($validator->fails()) {
            $this->addErrors($validator->message());
        }

        return $this;
    }

    private function implicit(): ImplicitValidator
    {
        return new ImplicitValidator($this->rules, $this->user, $this->params);
    }

    private function addErrors(string $message): void
    {
        $message = $this->row->has($this->errorColumn)
            ? "{$this->row->get($this->errorColumn)} | {$message}"
            : $message;

        $this->row->set($this->errorColumn, $message);
    }
}
