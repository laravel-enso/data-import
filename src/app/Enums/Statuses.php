<?php

namespace LaravelEnso\DataImport\app\Enums;

use LaravelEnso\IO\app\Enums\IOStatuses;

class Statuses extends IOStatuses
{
    const Processed = 23;
    const ExportingRejected = 26;

    protected static $data = [
        IOStatuses::Waiting => 'Waiting',
        IOStatuses::Processing => 'Processing',
        self::Processed => 'Processed',
        self::ExportingRejected => 'Exporting Rejected',
        IOStatuses::Finalized => 'Finalized',
    ];
}
