<?php

namespace LaravelEnso\DataImport\app\Classes\Attributes;

class Sheet
{
    const Mandatory = ['name', 'columns', 'importerClass'];

    const Optional = ['validatorClass', 'chunkSize'];
}
