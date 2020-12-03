<?php

namespace LaravelEnso\DataImport\Exceptions;

use LaravelEnso\Helpers\Exceptions\EnsoException;

class Route extends EnsoException
{
    public static function notFound(string $route)
    {
        return new static(__(
            'route does not exist: ":route"',
            ['route' => $route]
        ));
    }
}
