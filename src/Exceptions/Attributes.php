<?php

namespace LaravelEnso\DataImport\Exceptions;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\Exceptions\EnsoException;

class Attributes extends EnsoException
{
    public static function missing(Collection $attributes, $class)
    {
        return new static(__(
            'The following attributes are mandatory for :class : ":attrs"',
            ['attrs' => $attributes->implode('", "'), 'class' => $class],
        ));
    }

    public static function unknown(Collection $attributes, $class)
    {
        return new static(__(
            'The following optional attributes are allowed for :class : ":attrs"',
            ['attrs' => $attributes->implode('", "'), 'class' => $class]
        ));
    }
}
