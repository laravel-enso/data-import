<?php

namespace LaravelEnso\DataImport\app\Classes;

use LaravelEnso\DataImport\app\Classes\Reporting\ImportSummary;
use LaravelEnso\DataImport\app\Classes\Wrappers\Workbook;

class ImportConfiguration
{
    private $config;

    private const MaxExecutionTime = 60;
    private const SheetEntriesLimit = 5000;
    private const StopOnErrors = false;

    public function __construct(string $type)
    {
        $this->config = $this->getConfiguration($type);
        $this->setMaxExecutionTime();
    }

    public function getTemplate()
    {
        return isset($this->config['template'])
            ? $this->resolveTemplateInstance()
            : $this->throwMissingParamException('template');
    }

    public function getImporter(Workbook $sheets, ImportSummary $summary)
    {
        return isset($this->config['importerClass'])
            ? new $this->config['importerClass']($sheets, $summary)
            : $this->throwMissingParamException('importerClass');
    }

    public function getCustomValidator(Workbook $sheets, ImportSummary $summary)
    {
        return isset($this->config['customValidatorClass'])
            ? new $this->config['customValidatorClass']($this->getTemplate(), $sheets, $summary)
            : null;
    }

    public function getSheetEntriesLimit()
    {
        return isset($this->config['sheetEntriesLimit'])
            ? $this->config['sheetEntriesLimit']
            : self::SheetEntriesLimit;
    }

    public function getStopOnErrors()
    {
        return isset($this->config['stopOnErrors'])
            ? $this->config['stopOnErrors']
            : self::StopOnErrors;
    }

    private function getConfiguration(string $type)
    {
        return collect(config('importing.configs'))->first(
            function ($config, $key) use ($type) {
                return $key === $type;
            }
        );
    }

    private function setMaxExecutionTime()
    {
        ini_set(
            'max_execution_time',
            isset($this->config['max_execution_time'])
                ? $this->config['max_execution_time']
                : self::MaxExecutionTime
        );
    }

    private function readJsonTemplate()
    {
        if (!\File::exists(base_path($this->config['template']))) {
            throw new \EnsoException(
                __('Template file is missing').': '.base_path($this->config['template'])
            );
        }

        return \File::get(base_path($this->config['template']));
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
