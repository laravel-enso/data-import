<?php

namespace LaravelEnso\DataImport\app\Classes\Reporting;

class StructureIssue
{
    public $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
