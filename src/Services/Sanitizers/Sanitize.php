<?php

namespace LaravelEnso\DataImport\Services\Sanitizers;

use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Cell\FormulaCell;
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
            ->map(fn ($cell) => self::cell($cell))
            ->slice(0, $length)
            ->pad($length, null)
            ->toArray();
    }

    public static function name(string $name): string
    {
        return Str::of($name)->lower()->snake();
    }

    private static function cell(Cell $cell)
    {
        $value = $cell instanceof FormulaCell
            ? $cell->getComputedValue()
            : $cell->getValue();

        if ($value instanceof DateTime) {
            return Carbon::instance($value)->toDateTimeString();
        }

        if (is_string($value)) {
            $value = Str::of($value)->trim();
            $to = 'UTF-8';
            $from = mb_detect_encoding($value, ['auto']);

            if (! $from) {
                $value = '';
            } elseif ($from !== $to) {
                $value = mb_convert_encoding($value, $to, $from);
            }
        }

        return $value === '' ? null : $value;
    }
}
