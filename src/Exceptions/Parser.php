<?php

namespace LaravelEnso\DataImport\Exceptions;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\Exceptions\EnsoException;

class Parser extends EnsoException
{
    public static function missingAttributes(Collection $attributes, $class)
    {
        return new static(__(
            'The following attributes are mandatory for :class : ":attrs"',
            ['attrs' => $attributes->implode('", "'), 'class' => $class],
        ));
    }

    public static function unknownAttributes(Collection $attributes, $class)
    {
        return new static(__(
            'The following optional attributes are allowed for :class : ":attrs"',
            ['attrs' => $attributes->implode('", "'), 'class' => $class]
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
