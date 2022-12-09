<?php

namespace LaravelEnso\DataImport\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use LaravelEnso\DataImport\Models\Import;
use Throwable;

class Options
{
    public function __invoke(): Collection
    {
        try {
            return Collection::wrap(self::types())
                ->map(fn ($type) => [
                    'id' => $type, 'name' => self::label($type),
                ]);
        } catch (Throwable) {
            throw Import::configNotReadable();
        }
    }

    public static function types(): array
    {
        return array_keys(self::configs());
    }

    public static function label(string $type): string
    {
        return self::configs()[$type]['label'];
    }

    private static function configs(): array
    {
        return Config::get('enso.imports.configs');
    }
}
