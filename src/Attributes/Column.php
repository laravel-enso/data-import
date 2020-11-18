<?php

namespace LaravelEnso\DataImport\Attributes;

class Column extends Attribute
{
    public const Mandatory = ['name'];

    public const Optional = ['validations', 'description'];
}
