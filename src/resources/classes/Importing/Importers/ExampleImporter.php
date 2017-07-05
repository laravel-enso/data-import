<?php

namespace App\Importing\Importers;

use LaravelEnso\DataImport\app\Classes\AbstractImporter;

class ExampleImporter extends AbstractImporter
{
    public function run()
    {
        $this->sheets->each(function ($sheet) {
            $sheet->each(function($row) {
            	//do custom import logic
                $this->incSuccess();
            });
        });
    }
}
