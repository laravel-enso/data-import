<?php

namespace LaravelEnso\DataImport\Attributes;

class Sheet extends Attribute
{
    public const Mandatory = ['name', 'columns', 'importerClass'];

    public const Optional = ['validatorClass', 'chunkSize', 'params'];
}
