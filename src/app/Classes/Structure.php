<?php

namespace LaravelEnso\DataImport\app\Classes;

use Illuminate\Http\UploadedFile;
use LaravelEnso\Helpers\app\Classes\JsonParser;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Classes\Reader\Structure as Reader;
use LaravelEnso\DataImport\app\Classes\Validators\Structure as Validator;

class Structure
{
    private $import;
    private $file;
    private $summary;
    private $template;

    public function __construct(DataImport $import, UploadedFile $file)
    {
        $this->import = $import;
        $this->file = $file;
        $this->summary = new Summary($this->file->getClientOriginalName());
        $this->template = new Template($this->templateFile());
    }

    public function validates()
    {
        (new Validator(
            $this->template, $this->structure(), $this->summary
        ))->run();

        return ! $this->summary->hasIssues();
    }

    public function summary()
    {
        return $this->summary;
    }

    public function template()
    {
        return $this->template;
    }

    private function structure()
    {
        return (new Reader($this->file))
            ->get();
    }

    private function templateFile()
    {
        return (new JsonParser($this->templatePath()))
            ->object();
    }

    private function templatePath()
    {
        return base_path(config(
            'enso.imports.configs.'.$this->import->type.'.template'
        ));
    }
}
