<?php

namespace LaravelEnso\DataImport\Services\Sanitizers;

use Box\Spout\Common\Entity\Cell;
use Box\Spout\Common\Entity\Row;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Sanitize
{
    public static function header(Row $header): Collection
    {
        return Collection::wrap($header->getCells())
            ->map(fn (Cell $cell) => self::name($cell->getValue()));
    }

    public static function sheets(Collection $sheets): Collection
    {
        return $sheets->map(fn ($sheet) => self::name($sheet));
    }

    public static function cells(array $cells, int $length): array
    {
        return Collection::wrap($cells)
            ->map(fn ($cell) => self::cell($cell->getValue()))
            ->slice(0, $length)
            ->pad($length, null)
            ->toArray();
    }

    public static function name(string $name): string
    {
        return Str::of($name)->lower()->snake();
    }

    private static function cell($cell)
    {
        if ($cell instanceof DateTime) {
            return Carbon::instance($cell)->toDateTimeString();
        }

        if (is_string($cell)) {
            $from = mb_detect_encoding($cell, ['auto']);
            $to = 'UTF-8';

            if ($from !== $to) {
                $cell = mb_convert_encoding($cell, $to, $from);
            }

            $cell = Str::of($cell)->trim();
        }

        return $cell === '' ? null : $cell;
    }
}
