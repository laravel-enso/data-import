<?php

namespace LaravelEnso\DataImport\Enums;

use LaravelEnso\IO\Enums\IOStatuses;

class Statuses extends IOStatuses
{
    public const Processed = 23;
    public const ExportingRejected = 26;
    public const Cancelled = 40;

    protected static array $data = [
        IOStatuses::Waiting => 'Waiting',
        IOStatuses::Processing => 'Processing',
        self::Processed => 'Processed',
        self::ExportingRejected => 'Exporting Rejected',
        IOStatuses::Finalized => 'Finalized',
        self::Cancelled => 'Cancelled',
    ];

    public static function cancellable($status): bool
    {
        return in_array($status, [static::Waiting, static::Processing]);
    }
}
