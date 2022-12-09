<?php

namespace LaravelEnso\DataImport\Enums;

use LaravelEnso\Enums\Contracts\Frontend;
use LaravelEnso\Enums\Contracts\Mappable;

enum Status: int implements Frontend, Mappable
{
    case Waiting = 10;
    case Processing = 20;
    case Processed = 23;
    case ExportingRejected = 26;
    case Finalized = 30;
    case Cancelled = 40;

    public static function registerBy(): string
    {
        return 'importStatuses';
    }

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

    public function isDeletable(): bool
    {
        return in_array($this, self::deletable());
    }

    public function isRunning(): bool
    {
        return in_array($this, [self::Waiting, self::Processing]);
    }

    public static function deletable(): array
    {
        return [self::Finalized, self::Cancelled];
    }
}
