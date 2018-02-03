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
        if (!isset($this->template)) {
            $this->setTemplate();
        }

        return $this->template;
    }

    private function readConfig(string $type)
    {
        $this->config = config('enso.imports.'.$type);
    }

    private function setTemplate()
    {
        $this->template = new Template($this->jsonTemplate());
    }

    private function jsonTemplate()
    {
        return \File::get(base_path($this->config['template']));
    }
}
