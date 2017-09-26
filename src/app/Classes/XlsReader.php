<?php

namespace LaravelEnso\DataImport\app\Classes;

class XlsReader
{
    private $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function get()
    {
        return \Excel::load($this->file)->get();
    }
}
