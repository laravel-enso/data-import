<?php

namespace LaravelEnso\DataImport\app\Contracts;

use LaravelEnso\Helpers\app\Classes\Obj;

interface BeforeHook
{
    public function before(Obj $params);
}
