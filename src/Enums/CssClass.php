<?php

namespace LaravelEnso\DataImport\Enums;

use LaravelEnso\Enums\Contracts\Frontend;

enum CssClass:string implements Frontend
{
    case Waiting = 'is-info';
    case Processing = 'is-warning';
    case Processed = 'is-primary';
    case ExportingRejected = 'is-danger';
    case Finalized = 'is-success';
    case Cancelled = 'is-danger';

    public static function registerBy(): string
    {
        return 'importCssClasses';
    }
}
