<?php

namespace LaravelEnso\DataImport\app\Classes\Attributes;

class Column
{
    const Mandatory = ['name'];

    const Optional = ['laravelValidations', 'complexValidations'];
}
