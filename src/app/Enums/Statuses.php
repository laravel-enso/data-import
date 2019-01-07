<?php

namespace LaravelEnso\DataImport\app\Enums;

use LaravelEnso\Helpers\app\Classes\Enum;

class Statuses extends Enum
{
    const Waiting = 1;
    const Processing = 2;
    const Processed = 3;
    const ExportingRejected = 4;
    const Finalized = 5;

    protected static $data = [
        self::Waiting => 'Waiting',
        self::Processing => 'Processing',
        self::Processed => 'Processed',
        self::ExportingRejected => 'Exporting Rejected',
        self::Finalized => 'Finalized',
    ];
}
