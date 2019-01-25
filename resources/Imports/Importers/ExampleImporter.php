<?php

namespace App\Imports\Importers;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\DataImport\app\Contracts\Importable;
use LaravelEnso\DataImport\app\Contracts\AfterHook; // optional
use LaravelEnso\DataImport\app\Contracts\BeforeHook; // optional

class ExampleImporter implements Importable, BeforeHook, AfterHook
{
    public function before(Obj $params) // optional
    {
        // optional logic to be executed before the import is started
    }

    public function run(Obj $row, Obj $param)
    {
        // required import logic for each row
    }

    public function after(Obj $params) // optional
    {
        // optional logic to be executed after the import has finished
    }
}
