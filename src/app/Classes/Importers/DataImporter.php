<?php

namespace LaravelEnso\DataImport\app\Classes\Importers;

use Illuminate\Http\UploadedFile;
use LaravelEnso\DataImport\app\Classes\Summary;
use LaravelEnso\Helpers\app\Classes\JsonParser;
use LaravelEnso\DataImport\app\Classes\Template;
use LaravelEnso\DataImport\app\Classes\Reader\XLSXReader;
use LaravelEnso\DataImport\app\Classes\Validator as ImportValidator;
use LaravelEnso\DataImport\app\Classes\Validators\Template as TemplateValidator;

final class DataImporter
{
    private $file;
    private $type;
    private $template;
    private $summary;
    private $sheets;

    public function __construct(UploadedFile $file, string $type)
    {
        $this->file = $file;
        $this->type = $type;
        $this->summary = new Summary($file->getClientOriginalName());
    }

    public function handle()
    {
        $this->readTemplate()
            ->setMaxExecutionTime()
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
        return ! $this->cannotImport();
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
        (new ImportValidator(
            $this->template,
            $this->sheets,
            $this->summary
        ))->run();
    }

    private function readTemplate()
    {
        $json = $this->jsonParser()->object();

        if ($this->needsValidation()) {
            (new TemplateValidator($json))
                ->run();
        }

        $this->template = new Template($json);

        return $this;
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

    private function jsonParser()
    {
        return new JsonParser($this->filename());
    }

    private function filename()
    {
        return base_path(
            config('enso.imports.configs.'.$this->type.'.template')
        );
    }

    private function needsValidation()
    {
        return ! app()->environment('production')
            || config('enso.imports.validations') === 'always';
    }
}
