<?php

namespace LaravelEnso\DataImport\app\Classes\Importers;

use LaravelEnso\DataImport\app\Classes\Config;
use LaravelEnso\DataImport\app\Classes\Summary;
use LaravelEnso\DataImport\app\Classes\Validator;
use LaravelEnso\DataImport\app\Classes\Reader\XLSXReader;

final class DataImporter
{
    protected $template;
    protected $summary;
    protected $sheets;

    public function __construct(array $file, string $type)
    {
        $this->template = (new Config($type))->template();
        $this->summary = new Summary($file['original_name']);
        $this->readSheets($file['full_path']);
    }

    public function run()
    {
        $this->setMaxExecutionTime();

        (new Validator(
            $this->template,
            $this->sheets,
            $this->summary
        ))->run();

        if ($this->cannotImport()) {
            return;
        }

        $this->importer()->run();
    }

    public function fails()
    {
        return $this->cannotImport() || $this->noneImported();
    }

    public function summary()
    {
        return $this->summary;
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

    private function setMaxExecutionTime()
    {
        ini_set(
            'max_execution_time',
            $this->template->maxExecutionTime()
        );
    }

    private function importer()
    {
        $importerClass = $this->template->importer();

        return new $importerClass($this->sheets, $this->summary);
    }

    private function readSheets($file)
    {
        $this->sheets = (new XLSXReader($file))
            ->sheets();
    }
}
