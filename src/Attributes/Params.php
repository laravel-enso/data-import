<?php

namespace LaravelEnso\DataImport\Attributes;

class Params extends Attributes
{
    protected array $mandatory = ['name', 'type'];

    protected array $optional = [
        'validations', 'multiple', 'route', 'params', 'custom',
        'label',  'value', 'selectLabel',
    ];

    protected array $dependent = ['select' => ['route']];
}
