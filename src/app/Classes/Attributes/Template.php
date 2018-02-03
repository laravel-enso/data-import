<?php

namespace LaravelEnso\DataImport\app\Classes\Attributes;

class Template
{
    const Mandatory = ['sheets', 'importerClass'];

    const Optional = ['validatorClass', 'entryLimit', 'stopsOnIssues', 'maxExecutionTime'];
}
