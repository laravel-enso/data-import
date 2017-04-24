<?php
/**
 * Created by PhpStorm.
 * User: mihai
 * Date: 24.02.2017
 * Time: 16:08
 */

namespace LaravelEnso\DataImport\app\Classes;

use LaravelEnso\DataImport\app\Classes\Reporting\ValidationSummary;
use LaravelEnso\DataImport\app\Classes\Validators\BaseValidator;

class Import
{
    protected $template;
    protected $xlsx;
    protected $summary;
    protected $validator;
    protected $importer;

    public function __construct(int $type, $file)
    {
        ini_set('max_execution_time', 180);

        $this->xslx      = $this->getXlsx($file);
        $config          = new ImportConfigurationReader($type);
        $this->summary   = new ValidationSummary($file->getClientOriginalName());
        $this->template  = new $config->templateClass();
        $this->validator = new BaseValidator($this->template, $this->xlsx, $this->summary, $config->customValidatorClass, $config->sheetEntriesLimit);
        $this->importer = new $config->importerClass($this->xlsx, $this->summary);
    }

    public function run()
    {
        $this->validator->run();

        if ($this->validator->isValid()) {
            $this->processImport();
        }

        return $this->validator->getSummary();
    }

    private function getXlsx($file)
    {
        $this->validateFile($file);
        $this->validateExtension($file);
        $this->xlsx = \Excel::load($file)->get();
    }

    private function validateFile($file)
    {
        if (!$file->isValid()) {
            throw new \EnsoException("The file is not valid");
        }
    }

    private function validateExtension($file)
    {
        if ($file->getClientOriginalExtension() !== 'xlsx') {
            throw new \EnsoException('The file must have "xlsx" extension');
        }
    }

    private function processImport()
    {
        $this->importer->run();
    }

    public function isValid()
    {
        return $this->validator->isValid();
    }

    public function getSummary()
    {
        return $this->validator->getSummary();
    }
}
