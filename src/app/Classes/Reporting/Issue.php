<?php

namespace LaravelEnso\DataImport\app\Classes\Reporting;

use LaravelEnso\Helpers\Classes\AbstractObject;

class Issue extends AbstractObject
{
    public $category;
    public $value;
    public $rowNumber;
    public $column;

    public function __construct(array $properties)
    {
        foreach ($properties as $property => $value) {
            if (!property_exists($this, $property)) {
                throw new \EnsoException(
                    __('Property').': '.$property.' '.__('not defined in').': '.get_class($this)
                );
            }

            $this->$property = $value;
        }
    }
}
