<?php
/**
 * Created by PhpStorm.
 * User: mihai
 * Date: 21.02.2017
 * Time: 17:29
 */

namespace LaravelEnso\DataImport\app\Classes\Reporting;

class SheetIssue
{
    public $rowNumber;
    public $column;
    public $value;

    public function __construct(int $rowNumber, string $column, $value)
    {
        $this->rowNumber = $rowNumber;
        $this->column    = $column;
        $this->value     = $value;
    }
}
