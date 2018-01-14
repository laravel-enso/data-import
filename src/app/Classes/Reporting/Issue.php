<?php

namespace LaravelEnso\DataImport\app\Classes\Reporting;

use LaravelEnso\Helpers\app\Classes\Obj;

class Issue extends Obj
{
    public $value;
    public $rowNumber;
    public $column;

    public function __construct(array $properties)
    {
        foreach ($properties as $property => $value) {
            if (!property_exists($this, $property)) {
                throw new \Exception(
                    __('Property').': '.$property.' '.__('not defined in').': '.get_class($this)
                );
            }

            $this->$property = $value;
        }
    }
}
