<?php

namespace LaravelEnso\DataImport\Attributes;

class Sheet
{
    public const Mandatory = ['name', 'columns', 'importerClass'];

    public const Optional = ['validatorClass', 'chunkSize', 'params'];
}
