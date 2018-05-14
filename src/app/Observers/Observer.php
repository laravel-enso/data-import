<?php

namespace LaravelEnso\DataImport\app\Observers;

use LaravelEnso\DataImport\app\Classes\Handlers\Destroyer;

class Observer
{
    public function deleting($model)
    {
        (new Destroyer($model))->run();
    }
}
