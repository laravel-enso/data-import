<?php

namespace LaravelEnso\DataImport\App\Enums;

use LaravelEnso\IO\App\Enums\IOStatuses;

class Statuses extends IOStatuses
{
    public const Processed = 23;
    public const ExportingRejected = 26;

    protected static array $data = [
        IOStatuses::Waiting => 'Waiting',
        IOStatuses::Processing => 'Processing',
        self::Processed => 'Processed',
        self::ExportingRejected => 'Exporting Rejected',
        IOStatuses::Finalized => 'Finalized',
    ];
}
