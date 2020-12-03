<?php

namespace LaravelEnso\DataImport\Services\Validators\Params;

use Illuminate\Support\Facades\Route as Routes;
use LaravelEnso\DataImport\Attributes\Params;
use LaravelEnso\DataImport\Exceptions\Attributes;
use LaravelEnso\DataImport\Exceptions\Route;
use LaravelEnso\Helpers\Services\Obj;

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
        (new Params())->validateMandatory($this->param->keys())
            ->rejectUnknown($this->param->keys());

        return $this;
    }

    private function complementaryAttributes(): self
    {
        $attributes = (new Params());

        $attributes->dependent($this->param->get('type'))
            ->reject(fn ($attr) => $this->param->has($attr))
            ->unlessEmpty(
                fn ($missing) => Attributes::missing($missing, $attributes->class())
            );

        return $this;
    }

    private function route(): void
    {
        $route = $this->param->get('route');

        if ($route !== null && ! Routes::has($route)) {
            throw Route::notFound($route);
        }
    }
}
