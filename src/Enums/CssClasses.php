<?php

namespace LaravelEnso\DataImport\Enums;

enum CssClasses: int implements Frontend, Mappable
{
    case Waiting = 10;
    case Processing = 20;
    case Processed = 23;
    case ExportingRejected = 26;
    case Finalized = 30;
    case Cancelled = 40;

    public static function registerBy(): string
    {
        return 'importCssClasses';
    }

    public function map(): string
    {
        return match ($this) {
            self::Waiting => 'is-info',
            self::Processing => 'is-warning',
            self::Processed => 'is-primary',
            self::ExportingRejected => 'is-danger',
            self::Finalized => 'is-success',
            self::Cancelled => 'is-danger',
        };
    }
}
