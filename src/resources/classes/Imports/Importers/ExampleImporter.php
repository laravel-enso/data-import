<?php

namespace App\Imports\Importers;

use LaravelEnso\DataImport\app\Classes\Importers\Importer;

class ExampleImporter extends Importer
{
    public function run()
    {
        //collection of rows with no issues from the specified sheet
        $this->rowsFromSheet('your-sheet-name')
            ->each(function ($row) {
                //import logic and successful count
                $this->incSuccess();
            });
    }
}
