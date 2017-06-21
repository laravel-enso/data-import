<?php

namespace LaravelEnso\DataImport\app\Enums;

use LaravelEnso\Helpers\Classes\AbstractEnum;

class ComplexValidationTypesEnum extends AbstractEnum
{
    public function __construct()
    {
        $this->data = [
            'exists_in_sheet'   => __('Value must exist in the sheet'),
            'unique_in_column'  => __('Value must be unique in its column'),
            'duplicate_lines'   => __('This sheet lines are doubles'),
        ];
    }
}
