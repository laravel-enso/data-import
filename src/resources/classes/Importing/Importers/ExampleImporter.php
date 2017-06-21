<?php

namespace App\Importing\Importers;

use LaravelEnso\DataImport\app\Classes\AbstractImporter;

class ExampleImporter extends AbstractImporter
{
    public function run()
    {
        $this->xlsx->each(function ($sheet, $index) {
            foreach ($sheet as $index => $row) {
                $this->incSuccess();
            }
        });
    }
}
