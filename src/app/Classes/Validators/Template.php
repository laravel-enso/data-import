<?php

namespace LaravelEnso\DataImport\app\Classes\Validators;

use LaravelEnso\DataImport\app\Exceptions\ConfigException;
use LaravelEnso\DataImport\app\Exceptions\TemplateException;
use LaravelEnso\DataImport\app\Classes\Template as JsonTemplate;
use LaravelEnso\DataImport\app\Classes\Attributes\Template as Attributes;

class Template
{
    private $type;
    private $template;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function run()
    {
        $this->validateTemplate();

        $this->checkMandatoryAttributes()
            ->checkOptionalAttributes();
    }

    private function validateTemplate()
    {
        $this->template = json_decode($this->jsonTemplate());

        if (!$this->template) {
            throw new TemplateException(__('Template is not readable'));
        }
    }

    private function jsonTemplate()
    {
        $file = base_path($this->templatePath());

        if (!\File::exists($file)) {
            throw new TemplateException(__(
                'Template :file is missing',
                ['file' => $file]
            ));
        }

        return \File::get($file);
    }

    private function templatePath()
    {
        $path = config('enso.imports.'.$this->type.'.template');

        if (!$path) {
            throw new ConfigException(__(
                'Template attribute is missing from the config for import: :type',
                ['type' => $this->type]
            ));
        }

        return $path;
    }

    private function checkMandatoryAttributes()
    {
        $diff = collect(Attributes::Mandatory)
            ->diff(collect($this->template)->keys());

        if ($diff->isNotEmpty()) {
            throw new ConfigException(__(
                'Mandatory Attribute(s) Missing in template: ":attr"',
                ['attr' => $diff->implode('", "')]
            ));
        }

        return $this;
    }

    private function checkOptionalAttributes()
    {
        $attributes = collect(Attributes::Mandatory)
            ->merge(Attributes::Optional);

        $diff = collect($this->template)
            ->keys()
            ->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw new ConfigException(__(
                'Unknown Attribute(s) Found in template: ":attr"',
                ['attr' => $diff->implode('", "')]
            ));
        }

        return $this;
    }
}
