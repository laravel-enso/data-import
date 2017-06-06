<?php
/**
 * Created by PhpStorm.
 * User: mihai
 * Date: 22.02.2017
 * Time: 16:05.
 */

namespace App\Importing\Importers;

use LaravelEnso\DataImport\app\Classes\AbstractImporter;

class ExampleImporter extends AbstractImporter
{
    public function run()
    {
        //import logic
        //use incSuccess() method for registering successful operations

        $this->xlsx->each(function ($sheet, $index) {
            foreach ($sheet as $index => $row) {

                //persist, then
                $this->incSuccess();
            }
        });
    }
}
