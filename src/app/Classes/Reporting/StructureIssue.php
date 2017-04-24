<?php
/**
 * Created by PhpStorm.
 * User: mihai
 * Date: 21.02.2017
 * Time: 17:29.
 */

namespace LaravelEnso\DataImport\app\Classes\Reporting;

class StructureIssue
{
    public $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
