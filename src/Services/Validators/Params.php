<?php

namespace LaravelEnso\DataImport\Services\Validators;

use Illuminate\Support\Facades\Route as Routes;
use LaravelEnso\DataImport\Attributes\Params as Attributes;
use LaravelEnso\DataImport\Exceptions\Attributes as Exception;
use LaravelEnso\DataImport\Exceptions\Route;
use LaravelEnso\Helpers\Services\Obj;

class Params
{
    private ?Obj $params;
    private Attributes $attributes;

    public function __construct(Obj $template)
    {
        $this->params = $template->get('params');
        $this->attributes = new Attributes();
    }

    public function run(): void
    {
        optional($this->params)
            ->each(fn ($param) => $this->validate($param));
    }

    public function validate(Obj $param): void
    {
        $this->attributes($param)
            ->complementaryAttributes($param)
            ->route($param)
            ->values($param);
    }

    private function attributes(Obj $param): self
    {
        $this->attributes->validateMandatory($param->keys())
            ->rejectUnknown($param->keys());

        return $this;
    }

    private function complementaryAttributes(Obj $param): self
    {
        $this->attributes->dependent($param->get('type'))
            ->reject(fn ($attr) => $param->has($attr))
            ->unlessEmpty(function ($missing) {
                throw Exception::missing($missing, $this->attributes->class());
            });

        return $this;
    }

    private function route(Obj $param): self
    {
        $route = $param->get('route');

        if ($route !== null && ! Routes::has($route)) {
            throw Route::notFound($route);
        }

        return $this;
    }

    private function values(Obj $param)
    {
        $allowed = $this->attributes->values('type');
        $valid = $allowed->contains($param->get('type'));

        if (! $valid) {
            throw Exception::invalidParam($allowed, $this->attributes->class());
        }
    }
}
