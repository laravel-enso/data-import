<?php

namespace LaravelEnso\DataImport\Attributes;

class CSV extends Attributes
{
    protected array $mandatory = [
        'timeout', 'fieldDelimiter', 'fieldEnclosure', 'importerClass', 'name',
        'columns',
    ];

    protected array $optional = [
        'validatorClass', 'chunkSize', 'params',
    ];
}
