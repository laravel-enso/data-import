<?php

namespace LaravelEnso\DataImport\Attributes;

use LaravelEnso\DataImport\Contracts\FieldValidation as Contract;

class Param implements Contract
{
    use FieldValidation;

    public const Mandatory = ['name', 'type'];

    public const Optional = ['validations', 'multiple', 'route', 'params', 'custom', 'label',  'value'];

    public const Dependent = [
        'select' => ['route']
    ];

}
