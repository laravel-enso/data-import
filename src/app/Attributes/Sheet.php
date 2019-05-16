<?php

namespace LaravelEnso\DataImport\app\Attributes;

class Sheet
{
    const Mandatory = ['name', 'columns', 'importerClass'];

    const Optional = ['validatorClass', 'chunkSize'];
}
