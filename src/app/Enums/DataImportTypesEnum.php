<?php
/**
 * Created by PhpStorm.
 * User: mihai
 * Date: 27.07.2016
 * Time: 9:19
 */

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
        $list = [];

        foreach ($importTypeConfigs as $value) {
            $list[$value['type']] = $value['uiLabel'];
        }

        return $list;
    }
}
