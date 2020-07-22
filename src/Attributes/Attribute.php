<?php

namespace LaravelEnso\DataImport\Attributes;

use Illuminate\Support\Collection;
use LaravelEnso\DataImport\Exceptions\Parser;
use ReflectionClass;

class Attribute
{
    public static function mandatory(): Collection
    {
        return new Collection(static::Mandatory ?? []);
    }

    public static function optional(): Collection
    {
        return new Collection(static::Optional ?? []);
    }

    public static function dependent($type): Collection
    {
        return new Collection(static::Dependent[$type] ?? []);
    }

    public static function allowed(): Collection
    {
        return static::mandatory()->concat(static::optional());
    }

    public static function missingAttributes(Collection $attributes): self
    {
        $missing = static::mandatory()
            ->diff($attributes);

        throw_if($missing->isNotEmpty(), Parser::missingAttributes($missing, static::class()));

        return new static();
    }

    public static function unknownAttributes(Collection $attributes): self
    {
        $unknown = $attributes->diff(static::allowed());

        throw_if($unknown->isNotEmpty(), Parser::unknownAttributes($unknown, static::class()));

        return new static();
    }

    private static function class(): string
    {
        $class = (new ReflectionClass(static::class))->getShortName();

        return strtolower($class);
    }
}
