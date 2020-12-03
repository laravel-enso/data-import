<?php

namespace LaravelEnso\DataImport\Attributes;

use Illuminate\Support\Collection;
use LaravelEnso\DataImport\Exceptions\Attributes as Exception;
use ReflectionClass;

class Attributes
{
    protected array $mandatory = [];

    protected array $optional = [];

    protected array $dependent = [];

    public function allowed(): Collection
    {
        return $this->mandatory()->concat($this->optional());
    }

    public function validateMandatory(Collection $attributes): self
    {
        $this->mandatory()->diff($attributes)->unlessEmpty(
            fn ($missing) => Exception::missing($missing, $this->class())
        );

        return $this;
    }

    public function rejectUnknown(Collection $attributes): self
    {
        $attributes->diff($this->allowed())->unlessEmpty(
            fn ($unknown) => Exception::unknown($unknown, $this->class())
        );

        return $this;
    }

    public function dependent($type): Collection
    {
        return new Collection($this->dependent[$type] ?? []);
    }

    public function class(): string
    {
        $class = (new ReflectionClass(static::class))->getShortName();

        return strtolower($class);
    }

    protected function mandatory(): Collection
    {
        return new Collection($this->mandatory);
    }

    protected function optional(): Collection
    {
        return new Collection($this->optional);
    }
}
