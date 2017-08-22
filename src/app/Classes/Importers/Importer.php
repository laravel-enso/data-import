<?php

namespace LaravelEnso\DataImport\app\Classes\Importers;

use LaravelEnso\DataImport\app\Classes\ImportConfiguration;
use LaravelEnso\DataImport\app\Classes\Reporting\ImportSummary;
use LaravelEnso\DataImport\app\Classes\Validators\BaseValidator;

class Importer
{
    protected $summary;
    protected $validator;
    protected $importer;
    protected $skipsContentErrors;

    public function __construct(string $type, $file)
    {
        ini_set('max_execution_time', 180);

        $config = new ImportConfiguration($type);
        $sheets = $this->loadXlsx($file['full_path']);

        $this->skipsContentErrors = !$config->getStopOnErrors();
        $this->summary = new ImportSummary($file['original_name']);
        $this->validator = new BaseValidator($config, $sheets, $this->summary);
        $this->importer = $config->getImporter($sheets, $this->summary);
    }

    public function run()
    {
        $this->validator->run();

        if ($this->validator->isValid() || $this->canRunWithErrors()) {
            $this->importer->run();
        }
    }

    public function fails()
    {
        return $this->validator->hasStructureErrors()
            || ($this->validator->hasContentErrors() && !$this->skipsContentErrors);
    }

    public function canRunWithErrors()
    {
        return !$this->validator->hasStructureErrors()
            && $this->skipsContentErrors;
    }

    public function getSummary()
    {
        return $this->summary;
    }

    private function loadXlsx($file)
    {
        return \Excel::load($file)->get();
    }
}
