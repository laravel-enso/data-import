<?php

namespace LaravelEnso\DataImport\app\Classes\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\DataImport\app\Classes\Summary;
use LaravelEnso\DataImport\app\Classes\Template;

abstract class Validator
{
    protected $template;
    protected $sheets;
    protected $summary;

    public function __construct(Template $template, Collection $sheets, Summary $summary)
    {
        $this->template = $template;
        $this->sheets = $sheets;
        $this->summary = $summary;
    }

    abstract public function run();

    protected function summary()
    {
        return $this->summary;
    }

    protected function sheet(string $sheetName)
    {
        return $this->sheets
            ->first(function ($sheet) use ($sheetName) {
                return $sheet->name() === $sheetName;
            });
    }
}
