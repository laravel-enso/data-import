<?php

namespace LaravelEnso\DataImport\app\Classes;

class Config
{
    private $config;
    private $template;

    public function __construct(string $type)
    {
        $this->readConfig($type);
    }

    public function template()
    {
        return $this->template
            ?? $this->template = new Template($this->jsonTemplate());
    }

    private function readConfig(string $type)
    {
        $this->config = config('enso.imports.'.$type);
    }

    private function jsonTemplate()
    {
        return \File::get(base_path($this->config['template']));
    }
}
