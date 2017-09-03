<?php

namespace App\Importing\Importers;

use LaravelEnso\DataImport\app\Classes\Importers\AbstractImporter;

class ExampleImporter extends AbstractImporter
{
    public function run()
    {
        //this gives you just the rows without errors of that sheet
        $sheet = $this->getSheet('your-sheet-name');

        /*
         * $this->sheets gives you all the sheets but may include invalid rows
         * if the import is not configured to halt on errors
         * */

        $sheet->each(function ($row) {

            //do custom import logic
            $this->incSuccess();
        });
    }
}
