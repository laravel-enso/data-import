<?php

namespace LaravelEnso\DataImport\Attributes;

class Param extends Attribute
{
    public const Mandatory = ['name', 'type'];

    public const Optional = [
        'validations', 'multiple', 'route', 'params', 'custom', 'label',  'value', 'selectLabel',
    ];

    public const Dependent = [
        'select' => ['route'],
    ];
}
