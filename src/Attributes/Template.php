<?php

namespace LaravelEnso\DataImport\Attributes;

class Template extends Attributes
{
    protected array $mandatory = ['timeout'];

    protected array $optional = [
        'notifies', 'params', 'queue', 'sheets', 'fieldDelimiter',
        'fieldEnclosure',
    ];
}
