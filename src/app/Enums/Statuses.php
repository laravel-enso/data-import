<?php

namespace LaravelEnso\DataImport\app\Enums;

use LaravelEnso\IO\app\Enums\IOStatuses;

class Statuses extends IOStatuses
{
    const Processed = 23;
    const ExportingRejected = 26;

    protected static $data = [
        self::Waiting => 'Waiting',
        self::Processing => 'Processing',
        self::Processed => 'Processed',
        self::ExportingRejected => 'Exporting Rejected',
        self::Finalized => 'Finalized',
    ];
}
