<?php

namespace LaravelEnso\DataImport\app\Classes\Reporting;

class SheetIssue
{
    public $rowNumber;
    public $column;
    public $value;

    public function __construct(int $rowNumber, string $column, $value)
    {
        $this->rowNumber = $rowNumber;
        $this->column = $column;
        $this->value = $value;
    }
}
