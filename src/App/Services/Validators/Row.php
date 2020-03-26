<?php

namespace LaravelEnso\DataImport\App\Services\Validators;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator as Facade;
use LaravelEnso\Core\App\Models\User;
use LaravelEnso\Helpers\App\Classes\Obj;

class Row extends Validator
{
    private array $rules;
    private $validator;

    public function __construct(array $rules)
    {
        parent::__construct();

        $this->rules = $rules;
    }

    public function run(Obj $row, User $user, Obj $params): void
    {
        $this->validator = Facade::make($row->all(), $this->rules);

        if ($this->validator->fails()) {
            $this->addErrors();
        }
    }

    private function addErrors(): void
    {
        (new Collection($this->rules))->keys()
            ->filter(fn ($column) => $this->validator->errors()->has($column))
            ->each(fn ($column) => (new Collection($this->validator->errors()->get($column)))
                ->each(fn ($error) => $this->addError($error)));
    }
}
