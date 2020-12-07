<?php

namespace LaravelEnso\DataImport\Enums;

class CssClasses extends Statuses
{
    protected static array $data = [
        self::Waiting => 'is-info',
        self::Processing => 'is-warning',
        self::Processed => 'is-primary',
        self::ExportingRejected => 'is-danger',
        self::Finalized => 'is-success',
        self::Cancelled => 'is-danger',
    ];
}
