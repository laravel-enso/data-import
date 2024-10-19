<?php

namespace LaravelEnso\DataImport\Services\Sanitizers;

use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;

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
            $cell = Str::of($cell)->trim();
            $to = 'UTF-8';
            $from = mb_detect_encoding($cell, ['auto']);

            if (! $from) {
                $cell = '';
            } elseif ($from !== $to) {
                $cell = mb_convert_encoding($cell, $to, $from);
            }
        }

        return $cell === '' ? null : $cell;
    }
}
