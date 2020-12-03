<?php

namespace LaravelEnso\DataImport\Attributes;

class Column extends Attributes
{
    protected array $mandatory = ['name'];

    protected array $optional = ['validations', 'description'];
}
