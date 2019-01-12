<?php

namespace LaravelEnso\DataImport\app\Contracts;

use LaravelEnso\Helpers\app\Classes\Obj;

interface AfterHook
{
    public function after(Obj $params);
}
