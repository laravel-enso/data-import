<?php

namespace LaravelEnso\DataImport\app\Classes;

use Maatwebsite\Excel\Collections\SheetCollection;
use LaravelEnso\DataImport\app\Exceptions\ConfigException;
use LaravelEnso\DataImport\app\Exceptions\TemplateException;
use LaravelEnso\DataImport\app\Classes\Reporting\ImportSummary;

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

    public function getImporter(SheetCollection $sheets, ImportSummary $summary)
    {
        return isset($this->config['importerClass'])
            ? new $this->config['importerClass']($sheets, $summary)
            : $this->throwMissingParamException('importerClass');
    }

    public function getCustomValidator(SheetCollection $sheets, ImportSummary $summary)
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
        return collect(config('enso.imports'))->first(
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
            throw new TemplateException(__(
                'Template :file is missing',
                ['file' => base_path($this->config['template'])]
            ));
        }

        return \File::get(base_path($this->config['template']));
    }

    private function throwMissingParamException($param)
    {
        throw new ConfigException(__(
            'The parameter :param is missing from the config file',
            ['param' => $param]
        ));
    }

    private function resolveTemplateInstance()
    {
        return app()->makeWith('Template', ['template' => $this->readJsonTemplate()]);
    }
}
