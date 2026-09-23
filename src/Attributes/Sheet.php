<?php

namespace LaravelEnso\DataImport\Attributes;

class Sheet extends Attributes
{
    protected array $mandatory = ['name', 'columns', 'importerClass'];

    protected array $optional = ['validatorClass', 'chunkSize', 'params'];
}
