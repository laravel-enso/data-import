<?php
/**
 * Created by PhpStorm.
 * User: mihai
 * Date: 14.03.2017
 * Time: 15:53
 */

namespace LaravelEnso\DataImport\app\Classes;

class ImportConfigurationReader
{
    public $templateClass;
    public $importerClass;
    public $customValidatorClass;
    public $sheetEntriesLimit;

    public function __construct(int $type)
    {
        $config = $this->getConfigurationForType($type);
        $this->templateClass = $this->getTemplateClass($config);
        $this->importerClass = $this->getImporterClass($config);
        $this->customValidatorClass = $this->getCustomValidatorClass($config);
        $this->sheetEntriesLimit = $this->getSheetEntriesLimit($config);
    }

    public function getTemplateClass($config)
    {
        if (!$config['templateClass']) {
            throw new \EnsoException('"templateClass" parameter is missing from the config file');
        }

        return $config['templateClass'];
    }

    public function getImporterClass($config)
    {
        if (!$config['importerClass']) {
            throw new \EnsoException('"importerClass" parameter is missing from the config file');
        }

        return $config['importerClass'];
    }

    public function getCustomValidatorClass($config)
    {
        return isset($config['customValidatorClass']) ? $config['customValidatorClass'] : null;
    }

    public function getSheetEntriesLimit($config)
    {
        return isset($config['sheetEntriesLimit']) ? $config['sheetEntriesLimit'] : 5000;
    }

    private function getConfigurationForType(int $type)
    {
        $importTypeConfigs = $this->readConfigurationFile();
        $configuration     = null;

        foreach ($importTypeConfigs as $config) {

            if ($config['type'] === $type) {
                $configuration = $config;

                break;
            }
        }

        if (!$configuration) {
            throw new \EnsoException('Invalid configuration file');
        }

        return $configuration;
    }

    private function readConfigurationFile()
    {
        return (config('importing')['importTypeConfigs']);
    }
}
