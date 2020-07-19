<?php

namespace LaravelEnso\DataImport\Services\Validators\Params;

use LaravelEnso\DataImport\Services\Template;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\DataImport\Attributes\Param as Attribute;

class Structure
{
    private ?Obj $params;

    public function __construct(Template $template)
    {
        $this->params = $template->params();
    }

    public function validate(): void
    {
        if ($this->params) {
            $this->structure();
        }
    }

    private function structure(): self
    {
        $this->params->each(fn ($filter) => (new Param($filter, new Attribute()))->validate());

        return $this;
    }
}
