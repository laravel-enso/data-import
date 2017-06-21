<?php

namespace LaravelEnso\DataImport\app\Enums;

use LaravelEnso\Helpers\Classes\AbstractEnum;

class DataImportTypesEnum extends AbstractEnum
{
    public function __construct()
    {
        $this->data = $this->getDataFromConfig();
    }

    private function getDataFromConfig()
    {
        $importTypeConfigs = config('importing')['importTypeConfigs'];
        $data = [];

        foreach ($importTypeConfigs as $value) {
            $data[$value['type']] = __($value['uiLabel']);
        }

        return $data;
    }
}
