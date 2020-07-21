<?php

namespace LaravelEnso\DataImport\Services\Validators\Params;

use Illuminate\Support\Facades\Route;
use LaravelEnso\DataImport\Attributes\Param as Attribute;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\DataImport\Exceptions\Param as Exception;

class Param
{
    private Obj $param;

    public function __construct(Obj $param)
    {
        $this->param = $param;
    }

    public function validate(): void
    {
        $this->attributes()
            ->complementaryAttributes()
            ->route();
    }

    private function attributes(): self
    {
        Attribute::missingAttributes($this->param->keys())
            ->unknownAttributes($this->param->keys());

        return $this;
    }

    private function complementaryAttributes(): self
    {
        $missing = Attribute::dependent($this->param->get('type'))
            ->reject(fn ($attr) => $this->param->has('route'));

        throw_if($missing->isNotEmpty(), Exception::missingAttributes($missing));

        return $this;
    }

    private function route(): void
    {
        $route = $this->param->get('route');

        if ($route !== null && ! Route::has($route)) {
            throw Exception::routeNotFound($route);
        }
    }
}
