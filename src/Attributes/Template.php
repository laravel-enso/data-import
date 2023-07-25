<?php

namespace LaravelEnso\DataImport\Attributes;

class Template extends Attributes
{
    protected array $mandatory = ['sheets', 'timeout'];

    protected array $optional = ['notifies', 'params', 'queue'];
}
