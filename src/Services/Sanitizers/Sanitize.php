<?php

namespace LaravelEnso\DataImport\Services\Sanitizers;

use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Collection;

class Sanitize
{
    public static function handle(array $cells, int $length)
    {
        return Collection::wrap($cells)
            ->map(fn ($cell) => self::cell($cell->getValue()))
            ->slice(0, $length)
            ->pad($length, null);
    }

    private static function cell($cell)
    {
        if ($cell instanceof DateTime) {
            return Carbon::instance($cell)->toDateTimeString();
        }

        if (is_string($cell)) {
            $cell = trim($cell);
        }

        return $cell === '' ? null : $cell;
    }
}
