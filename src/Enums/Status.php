<?php

namespace LaravelEnso\DataImport\Enums;

use LaravelEnso\Enums\Contracts\Frontend;
use LaravelEnso\Enums\Contracts\Mappable;
use LaravelEnso\Enums\Contracts\Select;
use LaravelEnso\Enums\Traits\Select as Options;

enum Status: int implements Mappable, Select, Frontend
{
    use Options;

    case Waiting = 10;
    case Processing = 20;
    case Processed = 23;
    case ExportingRejected = 26;
    case Finalized = 30;
    case Cancelled = 40;

    public function map(): string
    {
        return match ($this) {
            self::Waiting => 'waiting',
            self::Processing => 'processing',
            self::Processed => 'processed',
            self::ExportingRejected => 'exporting rejected',
            self::Finalized => 'finalized',
            self::Cancelled => 'cancelled',
        };
    }

    public static function registerBy(): string
    {
        return 'importStatuses';
    }

    public static function isRunning(int $status): bool
    {
        return match ($status) {
            self::Waiting->value => true,
            self::Processing->value => true,
            self::Processed->value => false,
            self::ExportingRejected->value => false,
            self::Finalized->value => false,
            self::Cancelled->value => false,
        };
    }

    public static function isDeletable(int $status): bool
    {
        return match ($status) {
            self::Waiting->value => false,
            self::Processing->value => false,
            self::Processed->value => false,
            self::ExportingRejected->value => false,
            self::Finalized->value => true,
            self::Cancelled->value => true,
        };
    }

    public static function deletable(): array
    {
        return [self::Finalized->value, self::Cancelled->value];
    }
}
