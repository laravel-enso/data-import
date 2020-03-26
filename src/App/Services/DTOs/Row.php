<?php

namespace LaravelEnso\DataImport\App\Services\DTOs;

use LaravelEnso\Helpers\App\Classes\Obj;

class Row extends Obj
{
    public function hasContent(): bool
    {
        return $this->filter()->isNotEmpty();
    }

    public function isImportable(): bool
    {
        return ! $this->isRejected();
    }

    public function isRejected(): bool
    {
        return $this->has(config('enso.imports.errorColumn'));
    }
}
