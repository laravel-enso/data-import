<?php

namespace LaravelEnso\DataImport\app\Classes;

use LaravelEnso\DataImport\app\Classes\Reporting\ImportSummary;
use Maatwebsite\Excel\Collections\SheetCollection;

class ImportConfiguration
{
    private $config;

    private const SheetEntriesLimit = 5000;

    public function __construct(string $type)
    {
        $this->config = $this->getConfiguration($type);
    }

    public function getTemplate()
    {
        return isset($this->config['template'])
            ? $this->resolveTemplateInstance()
            : $this->throwMissingParamException('template');
    }

    public function getImporter(SheetCollection $sheets, ImportSummary $summary)
    {
        return isset($this->config['importerClass'])
            ? new $this->config['importerClass']($sheets, $summary)
            : $this->throwMissingParamException('importerClass');
    }

    public function getCustomValidator(SheetCollection $sheets, ImportSummary $summary)
    {
        return isset($this->config['customValidator'])
            ? new $this->config['customValidator']($this->getTemplate(), $sheets, $summary)
            : null;
    }

    public function getSheetEntriesLimit()
    {
        return isset($this->config['sheetEntriesLimit'])
            ? $this->config['sheetEntriesLimit']
            : self::SheetEntriesLimit;
    }

    private function getConfiguration(string $type)
    {
        return collect(config('importing.configs'))->first(
            function ($config, $key) use ($type) {
                return $key === $type;
            }
        );
    }

    private function readJsonTemplate()
    {
        return \File::get(app_path('Importing/Templates').DIRECTORY_SEPARATOR.$this->config['template']);
    }

    private function throwMissingParamException($param)
    {
        throw new \EnsoException(
            __(config('importing.validationLabels.missing_param_from_config')).': '.$param
        );
    }

    private function resolveTemplateInstance()
    {
        return app()->makeWith('Template', ['template' => $this->readJsonTemplate()]);
    }
}
