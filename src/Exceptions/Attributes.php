<?php

namespace LaravelEnso\DataImport\Exceptions;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\Exceptions\EnsoException;

class Attributes extends EnsoException
{
    public static function missing(Collection $attrs, string $class)
    {
        return new static(__(
            'The following attrs are mandatory for :class : ":attrs"',
            ['attrs' => $attrs->implode('", "'), 'class' => $class],
        ));
    }

    public static function unknown(Collection $attrs, string $class)
    {
        return new static(__(
            'The following optional attrs are allowed for :class : ":attrs"',
            ['attrs' => $attrs->implode('", "'), 'class' => $class]
        ));
    }

    public static function invalidParam(Collection $attrs, string $class)
    {
        return new static(__(
            'The following values are allowed for params types in :class : ":attrs"',
            ['attrs' => $attrs->implode('", "'), 'class' => $class]
        ));
    }
}
