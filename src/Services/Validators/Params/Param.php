<?php

namespace LaravelEnso\DataImport\Services\Validators\Params;

use Illuminate\Support\Facades\Route;
use LaravelEnso\DataImport\Contracts\FieldValidation as Attribute;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\DataImport\Exceptions\Param as Exception;

class Param
{
    private Obj $param;
    private Attribute $attribute;

    public function __construct(Obj $param, Attribute $attribute)
    {
        $this->param = $param;
        $this->attribute = $attribute;
    }

    public function validate(): void
    {
        $this->mandatoryAttributes()
            ->optionalAttributes()
            ->complementaryAttributes()
            ->route();
    }

    private function mandatoryAttributes(): self
    {
        $missing = $this->attribute->mandatory()
            ->diff($this->param->keys());

        throw_if($missing->isNotEmpty(), Exception::missingAttributes($missing));

        return $this;
    }

    private function optionalAttributes(): self
    {
        $unknown = $this->param->keys()
            ->diff($this->attribute->mandatory())
            ->diff($this->attribute->optional());

        throw_if($unknown->isNotEmpty(), Exception::unknownAttributes($unknown));

        return $this;
    }

    private function complementaryAttributes(): self
    {
        $missing = $this->attribute->dependent($this->param->get('type'))
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
