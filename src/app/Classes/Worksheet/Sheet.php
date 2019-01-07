<?php

namespace LaravelEnso\DataImport\app\Classes\Worksheet;

class Sheet
{
    private $name;
    private $header;

    public function __construct(string $name, array $header)
    {
        $this->name = $name;
        $this->header = collect($header);
    }

    public function name()
    {
        return $this->name;
    }

    public function header()
    {
        return $this->header;
    }
}
