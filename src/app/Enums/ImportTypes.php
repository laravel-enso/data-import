<?php

namespace LaravelEnso\DataImport\app\Enums;

use LaravelEnso\Helpers\Classes\AbstractEnum;

class ImportTypes extends AbstractEnum
{
    public function __construct()
    {
        $this->setData();
    }

    private function setData()
    {
        $this->data = array_combine(
            array_keys(config('importing.configs')),
            array_column(config('importing.configs'), 'label')
        );
    }
}
