<?php

namespace LaravelEnso\DataImport\Exceptions;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\Exceptions\EnsoException;

class Param extends EnsoException
{
    public static function missingAttributes(Collection $attributes)
    {
        return new static(__(
            'The following attributes are mandatory for params: ":attrs"',
            ['attrs' => $attributes->implode('", "')],
        ));
    }

    public static function unknownAttributes(Collection $attributes)
    {
        return new static(__(
            'The following optional attributes are allowed for params: ":attrs"',
            ['attrs' => $attributes->implode('", "')]
        ));
    }
    public static function routeNotFound(string $route)
    {
        return new static(__(
            'route does not exist: ":route"',
            ['route' => $route]
        ));
    }
}
