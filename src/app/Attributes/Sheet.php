<?php

namespace LaravelEnso\DataImport\App\Attributes;

class Sheet
{
    public const Mandatory = ['name', 'columns', 'importerClass'];

    public const Optional = ['validatorClass', 'chunkSize'];
}
