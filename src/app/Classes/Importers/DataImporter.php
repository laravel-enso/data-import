<?php

namespace LaravelEnso\DataImport\app\Classes\Importers;

use Illuminate\Http\UploadedFile;
use LaravelEnso\DataImport\app\Classes\Config;
use LaravelEnso\DataImport\app\Classes\Summary;
use LaravelEnso\DataImport\app\Classes\Validator;
use LaravelEnso\DataImport\app\Classes\Reader\XLSXReader;

final class DataImporter
{
    private $template;
    private $summary;
    private $file;
    private $sheets;

    public function __construct(UploadedFile $file, string $type)
    {
        $this->template = (new Config($type))->template();
        $this->summary = new Summary($file->getClientOriginalName());
        $this->file = $file;
    }

    public function run()
    {
        $this->setMaxExecutionTime()
            ->readSheets()
            ->validate();

        if ($this->canImport()) {
            $this->importer()->run();
        }
    }

    public function fails()
    {
        return $this->cannotImport() || $this->noneImported();
    }

    public function summary()
    {
        return $this->summary;
    }

    private function canImport()
    {
        return !$this->cannotImport();
    }

    private function cannotImport()
    {
        return $this->summary->hasStructureIssues()
            || ($this->summary->hasContentIssues() && $this->template->stopsOnIssues());
    }

    private function noneImported()
    {
        return $this->summary->successful() === 0;
    }

    private function validate()
    {
        (new Validator(
            $this->template,
            $this->sheets,
            $this->summary
        ))->run();
    }

    private function setMaxExecutionTime()
    {
        ini_set(
            'max_execution_time',
            $this->template->maxExecutionTime()
        );

        return $this;
    }

    private function importer()
    {
        $importerClass = $this->template->importer();

        return new $importerClass($this->sheets, $this->summary);
    }

    private function readSheets()
    {
        $this->sheets = (new XLSXReader($this->file))->sheets();

        return $this;
    }
}
