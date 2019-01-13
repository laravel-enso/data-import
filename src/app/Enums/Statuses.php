<?php

namespace LaravelEnso\DataImport\app\Enums;

use LaravelEnso\Helpers\app\Classes\Enum;

class Statuses extends Enum
{
    const Waiting = 10;
    const Processing = 20;
    const Processed = 23;
    const ExportingRejected = 26;
    const Finalized = 30;

    protected static $data = [
        self::Waiting => 'Waiting',
        self::Processing => 'Processing',
        self::Processed => 'Processed',
        self::ExportingRejected => 'Exporting Rejected',
        self::Finalized => 'Finalized',
    ];
}
