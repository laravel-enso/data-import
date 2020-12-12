<?php

namespace LaravelEnso\DataImport\Enums;

use LaravelEnso\Enums\Services\Enum;

class Statuses extends Enum
{
    public const Waiting = 10;
    public const Processing = 20;
    public const Processed = 23;
    public const ExportingRejected = 26;
    public const Finalized = 30;
    public const Cancelled = 40;

    protected static array $data = [
        self::Waiting => 'waiting',
        self::Processing => 'processing',
        self::Processed => 'processed',
        self::ExportingRejected => 'exporting rejected',
        self::Finalized => 'finalized',
        self::Cancelled => 'cancelled',
    ];

    public static function running(): array
    {
        return [static::Waiting, static::Processing];
    }

    public static function deletable(): array
    {
        return [static::Finalized, static::Cancelled];
    }
}
