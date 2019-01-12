<?php

namespace LaravelEnso\DataImport\app\Classes;

use Illuminate\Http\UploadedFile;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataImport\app\Classes\Reader\Structure as Reader;
use LaravelEnso\DataImport\app\Classes\Validators\Structure as Validator;

class Structure
{
    private $dataImport;
    private $file;
    private $summary;
    private $template;

    public function __construct(DataImport $dataImport, Template $template, UploadedFile $file)
    {
        $this->dataImport = $dataImport;
        $this->template = $template;
        $this->file = $file;
        $this->summary = new Summary($this->file->getClientOriginalName());
    }

    public function validates()
    {
        (new Validator(
            $this->template, $this->structure(), $this->summary
        ))->run();

        return ! $this->summary->hasErrors();
    }

    public function summary()
    {
        return $this->summary;
    }

    private function structure()
    {
        return (new Reader($this->file))
            ->get();
    }
}
