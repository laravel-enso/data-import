<?php

namespace LaravelEnso\DataImport\Attributes;

class Params extends Attributes
{
    protected array $mandatory = ['name', 'type'];

    protected array $optional = [
        'validations', 'multiple', 'route', 'params', 'custom',
        'label',  'value', 'selectLabel', 'placeholder', 'options',
    ];

    protected array $dependent = ['select' => ['route|options']];

    protected array $values = ['type' => ['select', 'input', 'checkbox', 'date']];
}
